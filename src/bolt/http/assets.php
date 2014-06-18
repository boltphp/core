<?php

namespace bolt\http;
use \b;


use Assetic\Asset\FileAsset,
    Assetic\Asset\HttpAsset,
    Assetic\Asset\AssetCollection,
    Assetic\Asset\GlobAsset,
    Assetic\Asset\StringAsset,
    Assetic\Asset\AssetCache,
    Assetic\Cache\FilesystemCache,
    Assetic\AssetManager,
    Assetic\FilterManager,
    Assetic\Factory\Worker\CacheBustingWorker,
    Assetic\Factory\AssetFactory;

use Symfony\Component\Finder\Finder;


/**
 * asset manager
 */
class assets implements \bolt\plugin\singleton {

    /**
     * @var bolt\http
     */
    private $_http;

    /**
     * @var array
     */
    private $_config = [];

    /**
     * @var string
     */
    private $_root = [];

    private $_path = [];

    /**
     * @var Assetic\AssetManager
     */
    private $_manager;

    private $_filter;

    private $_filters = [];

    private $_compile = [];

    private $_cache = false;

    private $_isCompiling = false;

    /**
     * Constructor
     *
     * @param bolt\http $http
     * @param array $config
     */
    public function __construct(\bolt\http $http, $config=[]) {
        $this->_http = $http;
        $this->_config = $config;

        $this->_root = $http->path($config['root']);

        $this->_filters = b::param('filters', [], $config);

        if (isset($config['path'])) {
            $this->_path = $config['path'];
        }

        if (isset($config['cache']['driver'])) {
            $this->setCache($config['cache']);
        }

        // if (isset($config['filters'])) {
        //     $this->filter($config['filters']);
        // }
        // if (isset($config['globals'])) {
        //     $this->globals($config['globals']);
        // }

        // compile events
        $http->app->on("compile", [$this, 'onCompile']);

        $this->_manager = new AssetManager();
        $this->_filter = new FilterManager();

        if (isset($config['ready'])) {
            call_user_func($config['ready'], $this);
        }

    }

    public function getConfig() {
        return $this->_config;
    }

    public function setCache(array $cache) {
        if (!isset($cache['driver']) || (isset($cache['drive']) && !is_subclass_of($cache['drive'], 'Doctrine\Common\Cache\Cache'))) {
            throw new \Exception("Cache driver must implment 'Doctrine\Common\Cache\Cache'.");
        }
        $this->_cache = $cache;
        return $this;
    }

    public function getCache() {
        return $this->_cache;
    }

    public function getCompiledFileInfo($path) {
        $compiled = isset($this->_http->app['compiled']) ? $this->_http->app['compiled']->get('assets') : [];
        if (isset($compiled['data']['map'][$path])) {
            return $compiled['data']['map'][$path];
        }
        return null;
    }

    public function getCompiledFile($path) {

        $compiled = isset($this->_http->app['compiled']) ? $this->_http->app['compiled']->get('assets') : [];
        if (isset($compiled['data']['map'][$path])) {
            return $this->_http->app['compiled']->getFile("assets/{$compiled['data']['map'][$path]['file']}");
        }
        else if (($file = $this->_http->app['compiled']->getFile("assets/{$path}")) !== null) {
            return $file;
        }
        return null;
    }

    /**
     * glob
     */
    public function glob($path, $filters = [], $root = null) {
        $root = $root ?: $this->_root;
        return new GlobAsset(b::path($root, $path), $filters, $root);
    }

    /**
     * file
     */
    public function file($path, $filters = [], $root = null) {
        $root = $root ?: $this->_root;
        if (is_array($path)) {
            return $this->collection(array_map(function($_) use ($filters, $root){
                return $this->file($_, $filters, $root);
            }, $path));
        }
        return new FileAsset(b::path($root, $path), $filters, $root);
    }

    /**
     * set
     */
    public function set($name, $files) {
        $this->_manager->set($name, $files);
    }

    public function collection($collection, $filters = [], $root = null, $vars = []) {
        $root = $root ?: $this->_root;
        return new AssetCollection($collection, $filters, $root, $vars);
    }

    public function factory($root = null, $manager = null, $filter = null, $debug = null) {
        $factory = new AssetFactory($root ?: $this->_root);
        $factory->setAssetManager($manager ?: $this->_manager);
        $factory->setFilterManager($filter ?: $this->_filter);
        $factory->setDebug($debug == null ? $debug : (b::env() == 'dev'));
        //$factory->addWorker(new CacheBustingWorker());
        return $factory;
    }


    public function url($path) {
        if (($info = $this->getCompiledFileInfo($path)) !== null) {
            $path = $info['file'];
        }

        if ($path{0} == '@') {
            $rel = str_replace("{name}", substr($path,1), $this->_path['collection']);
        }
        else {
            $rel = str_replace("{path}", $path, $this->_path['file']);
        }

        if (isset($this->_path['root'])) {
            return $this->_path['root'] . ltrim($rel, '/');
        }
        else {
            return $this->_http->request->getUriForPath($rel);
        }
    }


    public function getRoot() {
        return $this->_root;
    }

    public function __get($name) {
        if ($name == 'filters') {
            return $this->_filter;
        }

        return null;
    }

    public function path($path) {
        return b::path($this->_root, $path);
    }

    public function getFilters() {
        return $this->_filters;
    }

    public function compile($assets) {
        $this->_compile = array_merge($this->_compile, $assets);
        return $this;
    }

    public function isCompiling() {
        return $this->_isCompiling;
    }

    /**
     * compile assets into the compile directory
     *
     * @param bolt\events\event $e
     *
     * @return void
     */
    public function onCompile($e) {
        $dir = $e->data['client']->makeDir('assets');

        $this->_isCompiling = true;

        // loop through all the manager files
        $names = $this->_manager->getNames();

        $factory = $this->factory();

        $map = [];
        $files = [];

        // all compileds request
        foreach ($this->_compile as $asset) {

            foreach ($asset->all() as $item) {
                foreach ($asset->getFilters() as $filter) {
                    $item->ensureFilter($filter);
                }
                $d = $item->dump();
                $id = md5($d);
                $parts = pathinfo(b::path($item->getSourceRoot(), $item->getSourcePath()));
                $file = "{$parts['filename']}-{$id}.{$parts['extension']}";
                $map[$item->getSourcePath()] = [
                    'id' => $id,
                    'mtime' => $item->getLastModified(),
                    'file' => $file
                ];
                file_put_contents("{$dir}/{$file}", $d);
            }
        }

        foreach ($names as $name) {
            $asset = $this->_manager->get($name);

            $a = $factory->createAsset(
                    "@{$name}"
                );

            $ext = $asset->getVars()['ext'];
            $d = $a->dump();
            $id = md5($a->dump());
            $file = "{$name}-{$id}.{$ext}";
            $filters = isset($this->_filters[$ext]) ? $this->_filters[$ext] : [];

            if (isset($this->_filters['compile'][$ext])) {
                $filters = array_merge($filters, $this->_filters['compile'][$ext]);
            }

            // filters
            if (count($filters) > 0) {
                try {
                    $d = (new StringAsset($d, $filters, $this->getRoot()))->dump();
                }
                catch (\Exception $e) {
                    throw new \Exception("Unable to process filters {$e->getMessage()}");
                }
            }

            $map["@{$name}.{$ext}"] = [
                'id' => $id,
                'name' => $name,
                'mtime' => $a->getLastModified(),
                'file' => $file
            ];

            file_put_contents("{$dir}/{$file}", $d);

        }


        $finder = new Finder();
        $finder->files()
            ->ignoreVCS(true)
            ->name('*.css')
            ->in("$dir")
        ;

        // now loop through all compiled files and
        // rewrite any url paths
        foreach ($finder as $file) {
            $path = $file->getRealPath();
            $content = \Assetic\Util\CssUtils::filterUrls(file_get_contents($path), function($matches) use ($map) {
                $url = $matches['url'];
                if (empty($url) || stripos($url, 'http') !== false || substr($url,0,2) === '//' || stripos($url, 'data:') !== false) { return $matches[0]; }
                $rel = ltrim($matches[2], '/');
                if (isset($map[$rel])) {
                    return str_replace($matches['url'], $map[$rel]['file'], $matches[0]);
                }
            });
            file_put_contents($path, $content);
        }


        $e->data['client']->saveCompileLoader('assets', ['map' => $map, 'files' => $files]);


    }

}
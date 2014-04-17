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


    /**
     * @var Assetic\AssetManager
     */
    private $_manager;

    private $_filter;

    private $_filters = [];

    private $_compile = [];


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
            $http->bind('assets', 'bolt\http\middleware\assets', $config);
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

        // $this->_compiled = ($http->app['compiled'] ? $http->app['compiled']->get('assets') : []);

    }

    public function getCompiledFile($path) {
        if (isset($this->_compiled['data']['map'][$path])) {
            return $this->_http->app['compiled']->getFile("assets/{$this->_compiled['data']['map'][$path]['file']}");
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
        if (is_string($path) && $path{0} === '@') {
            $path = "collection/".substr($path,1);
        }
        return $this->_http->request->getUriForPath(str_replace('{path}', ltrim($path,'/'), rtrim($this->_config['path'],'/')));
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

    /**
     * compile assets into the compile directory
     *
     * @param bolt\events\event $e
     *
     * @return void
     */
    public function onCompile($e) {
        $dir = $e->data['client']->makeDir('assets');

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

            // filters
            if (array_key_exists($ext, $this->_filters)) {
                $d = (new StringAsset($d, $this->_filters[$ext], $this->getRoot()))->dump();
            }

            $map["@{$name}"] = [
                'id' => $id,
                'name' => $name,
                'mtime' => $a->getLastModified(),
                'file' => $file
            ];

            file_put_contents("{$dir}/{$file}", $d);

        }



        $e->data['client']->saveCompileLoader('assets', ['map' => $map, 'files' => $files]);


    }

    // public function compileFile($file, $rel = null, $filters = true) {
    //     $ext = pathinfo($file)['extension'];
    //     $o = false;

    //     if ($this->getGlobals($ext)) {

    //         // fm
    //         $fm = new AssetCollection([]);

    //         foreach ($this->getGlobals($ext) as $path) {
    //             if (is_string($path)) {
    //                 $fm->add( stripos($path, '*') !== false ? new GlobAsset($path) : new FileAsset($path) );
    //             }
    //         }

    //         $filter = new \bolt\http\assets\filters\cssRewrite($this);

    //         $fm->add(new FileAsset($file));

    //         $o = new StringAsset($fm->dump(), $this->getFilters($ext), dirname($file));

    //         $o->ensureFilter($filter);



    //     }
    //     else {
    //         $o = new FileAsset($file, $this->getFilters($ext));
    //     }

    //     return $o;

    // }

}
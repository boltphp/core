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
        $http->app->on("compile", [$this, 'compile']);

        $this->_manager = new AssetManager();
        $this->_filter = new FilterManager();

        if (isset($config['ready'])) {
            call_user_func($config['ready'], $this);
        }

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

    public function collection($collection, $filters = [], $root = null) {
        $root = $root ?: $this->_root;
        return new AssetCollection($collection, $filters, $root);
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


    // /**
    //  * register files to append to specific file type (by file extension)
    //  *
    //  * @param string|array $ext string of file exenstion or array of globals
    //  * @param string $path
    //  *
    //  * @return self
    //  */
    // public function globals($ext, $path = false) {
    //     if (is_array($ext)) {
    //         foreach ($ext as $item) {
    //             call_user_func_array([$this, 'globals'], $item);
    //         }
    //         return $this;
    //     }
    //     if (!array_key_exists($ext, $this->_globals)) { $this->_globals[$ext] = []; }
    //     $this->_globals[$ext][] = $this->_http->path($path);
    //     return $this;
    // }


    // /**
    //  * register a filter for a file type (by file extension)
    //  *
    //  * @param string|array $ext string of file extension or array of filters
    //  * @param string $class
    //  * @param array $args array of args passed to filter constuctor (via call_user_func_array)
    //  *
    //  * @return self
    //  */
    // public function filter($ext, $class=false, $args = []) {
    //     if (is_array($ext)) {
    //         foreach ($ext as $item) {
    //             call_user_func_array([$this, 'filter'], $item);
    //         }
    //         return $this;
    //     }
    //     if (stripos($ext, ',') !== false) {
    //         foreach (explode(',', $ext) as $_) {
    //             call_user_func_array([$this, 'filter'], [$_, $class, $args]);
    //         }
    //         return $this;
    //     }
    //     if (!array_key_exists($ext, $this->_filters)) { $this->_filters[$ext] = []; }
    //     $this->_filters[$ext][] = [
    //         'class' => $class,
    //         'instance' => false,
    //         'args' => $args,
    //         'ref' => b::getReflectionClass($class)
    //     ];

    //     return $this;
    // }


    // /**
    //  * add a new file to the manager
    //  *
    //  * @param string $type script or style
    //  * @param string $name name of group
    //  * @param array $files array of files
    //  *
    //  * @return self
    //  */
    // public function add($type, $name = false, $files = false) {
    //     if (is_array($type)) {
    //         foreach ($type as $item) {
    //             call_user_func_array([$this, 'add'], $item);
    //         }
    //         return $this;
    //     }

    //     $_ = $this->_groupName($type, $name);

    //     if (!array_key_exists($_, $this->_groups)) {
    //         $this->_groups[$_] = new assets\group($this, $name, $type);
    //     }

    //     // add some files
    //     $this->_groups[$_]->add($files);

    //     return $this;
    // }

    // public function createGroup($type, $name, $files = []) {
    //     $_ = $this->_groupName($type, $name);
    //     if (array_key_exists($_, $this->_groups)) { return $this->_groups[$_]; }
    //     $this->_groups[$_] = new assets\group($this, $name, $type);
    //     return $this->_groups[$_]->add($files);
    // }

    // private function _groupName($type, $name) {
    //     return implode("_", [$type, $name]);
    // }

    // /**
    //  * find a file in one of $dirs
    //  *
    //  * @param string $file relative file path
    //  *
    //  * @return mixed
    //  */
    // public function find($file) {


    //     foreach ($this->_dirs as $dir) {
    //         $path = $this->_http->path($dir, $file);
    //         if (file_exists($path)) {
    //             return [
    //                 'path' => $path,
    //                 'rel' => $this->_http->path($dir)
    //             ];
    //         }
    //     }

    //     return false;
    // }


    // /**
    //  * find a files dir
    //  *
    //  * @param string file
    //  *
    //  * @return mixed
    //  */
    // public function findDir($path) {
    //     $path = b::path($path);
    //     foreach ($this->_dirs as $dir) {
    //         $_ = $this->_http->path($dir);
    //         if (preg_match("#^".preg_quote($_,'#')."#", $path)) {
    //             return $_;
    //         }
    //     }
    //     return false;
    // }


    // /**
    //  * output a tag for give group
    //  *
    //  * @param string $type
    //  * @param string $group
    //  *
    //  * @return string
    //  */
    // public function out($type, $name, $combo = false) {
    //     $_ = $this->_groupName($type, $name);

    //     // is there a group
    //     if (!array_key_exists($_, $this->_groups)) {
    //         return null;
    //     }

    //     $group = $this->_groups[$_];

    //     $tags = [];

    //     foreach ($group as $file) {
    //         if($type == 'style') {
    //             $tags[] = '<link rel="stylesheet" href="'.$this->url($file).'" type="text/css">';
    //         }
    //     }

    //     return implode("", $tags);
    // }


    // /**
    //  * url path
    //  *
    //  * @param mixed $path
    //  *
    //  * @return string
    //  */
    // public function url($path) {
    //     if (is_a($path, 'Assetic\Asset\HttpAsset')) {
    //         return $path->getSourceRoot()."/".$path->getSourcePath();
    //     }

    //     if (is_a($path, 'Assetic\Asset\FileAsset')) {
    //         $path = b::path($path->getSourcePath());
    //     }
    //     if (is_string($path)) {
    //         return $this->_http->request->getUriForPath(str_replace('{path}', ltrim($path,'/'), rtrim($this->_config['path'],'/')));
    //     }
    // }


    // /**
    //  * get all file objects in a group
    //  *
    //  * @param string $type
    //  * @param string $group
    //  *
    //  * @return array
    //  */
    // public function getGroup($type, $name) {
    //     $_ = $this->_groupName($type, $name);
    //     return array_key_exists($_, $this->_groups) ? $this->_groups[$_] : null;
    // }


    // /**
    //  * return a stylesheet tag
    //  *
    //  * @param array $leafs
    //  *
    //  * @return string
    //  */
    // public function stylesheet($leafs) {
    //     if (is_string($leafs)) { $leafs = [$leafs]; }

    //     $output = [];

    //     foreach ($leafs as $leaf) {
    //         $output[] = str_replace('{path}', "{$leaf}", rtrim($this->_config['path'],'/'));
    //     }

    //     return implode("\n", array_map(function($href) {
    //         return '<link rel="stylesheet" href="'.$href.'" type="text/css">';
    //     }, $output));

    // }


    // /**
    //  * get all registered globals for an extension
    //  *
    //  * @param string $ext
    //  *
    //  * @return array
    //  */
    // public function getGlobals($ext) {
    //     return array_key_exists($ext, $this->_globals) ? $this->_globals[$ext] : [];
    // }


    // /**
    //  * get all registered filters for an extension
    //  *
    //  * @param string $ext
    //  *
    //  * @return array
    //  */
    // public function getFilters($ext, $when = null) {
    //     $items = [];
    //     if (array_key_exists($ext, $this->_filters)) {
    //         foreach ($this->_filters[$ext] as $i => $filter) {

    //             // make sure to only run when
    //             if ($when AND isset($filter['args']['when'])) {
    //                 if ( (is_string($filter['args']['when']) && $filter['args']['when'] !== $when) ||
    //                     (is_array($filter['args']['when']) && !in_array($when, $filter['args']['when']))
    //                 ) { continue; }
    //             }

    //             if (!$filter['instance']) {
    //                 $this->_filters[$ext][$i]['instance'] = $filter['instance'] = $filter['ref']->newInstanceArgs($filter['args']);

    //                 //
    //                 foreach ($this->_dirs as $dir) {
    //                     if (method_exists($this->_filters[$ext][$i]['instance'], 'addLoadPath')) {
    //                         $this->_filters[$ext][$i]['instance']->addLoadPath($this->_http->path($dir));
    //                     }
    //                 }

    //             }
    //             $items[] = $filter['instance'];
    //         }
    //     }
    //     return $items;
    // }



    // /**
    //  * compile assets into the compile directory
    //  *
    //  * @param bolt\events\event $e
    //  *
    //  * @return void
    //  */
    // public function compile($e) {
    //     $dir = $e->data['client']->makeDir('assets');

    //     // compiled map holder
    //     $map = [];
    //     $files = [];

    //     // loop through each of our dirs and
    //     // find any file that matches our needs
    //     foreach ($this->_dirs as $base) {
    //         $root = $this->_http->path($base);
    //         $assets = b::fs('glob', "{$root}/**/*.*")->asArray();

    //         foreach ($assets as $file) {
    //             $rel = str_replace($root, "", $file);
    //             $i = pathinfo($file);

    //             //
    //             $o = $this->compileFile($file);
    //             $dump = $o->dump();
    //             $id = md5($dump);
    //             $name = "{$i['filename']}-{$id}.{$i['extension']}";
    //             $map[$rel] = [
    //                 'id' => $id,
    //                 'name' => $name,
    //                 'mtime' => filemtime($file)
    //             ];
    //             $files[] = $name;

    //             // put our file in dir
    //             file_put_contents("{$dir}/{$name}", $dump);

    //         }

    //     }

    //     $e->data['client']->saveCompileLoader('assets', ['map' => $map, 'files' => $files]);


    // }

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
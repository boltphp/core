<?php

namespace bolt\http;
use \b;

use \Exception;

/**
 * views manager
 */
class views {

    /**
     * @var array
     */
    private $_dirs = [];

    /**
     * @var string
     */
    private $_class = 'bolt\http\views\file';


    /**
     * Constructor
     *
     * @param bolt\http $http
     * @param array $config
     */
    public function __construct(\bolt\http $http, $config = []) {

        $this->_http = $http;

        $this->_dirs = b::param('dirs', [], $config);

        // make sure we have a render plugin
        if (!$http->app->pluginExists('render')) {
            $http->app->plug('render', '\bolt\render');
        }

        if (isset($config['engines'])) {
            foreach ($config['engines'] as $engine) {
                $http->app['render']->engine($engine[0], $engine[1]);
            }
        }

        // when compile
        $http->app->on('compile', [$this, 'compile']);

    }

    /**
     * add a dir
     *
     * @param string $path
     * @param string $type type of dir to add
     *
     * @return self
     */
    public function dir($path) {
        if (is_array($path)) {
            foreach ($path as $item) {
                $this->dir($item);
            }
            return $this;
        }
        $this->_dirs[] = $path;
        return $this;
    }


    public function engine($ext, $class) {
        $this->_http->app['render']->engine($ext, $class);
        return $this;
    }

    public function getEngines() {
        return $this->_http->app['render']->getEngines();
    }

    /**
     * get view directories
     *
     * @return array
     */
    public function getDirs() {
        return $this->_dirs;
    }



    /**
     * find a view file in given dirs
     *
     * @param string $file
     * @param array $dirs
     *
     * @return string
     */
    public function find($file) {
        foreach ($this->_dirs as $dir) {
            $rel = b::path($dir, $file);
            $_ = $this->_http->path($rel);
            if (file_exists($_)){
                return $_;
            }
        }
        return false;
    }


    /**
     * check if a view exists
     *
     * @param string $file
     * @param array $dirs
     *
     * @return bool
     */
    public function exists($file, array $dirs = null) {
        return $this->find($file, $dirs) !== false;
    }


    /**
     * create a view of self::$_class
     *
     * @param string $file absolute file path
     * @param array $vars
     * @param object $context
     *
     * @return self::$_class
     */
    public function create($file, $vars = [], $context = false) {
        $class = $this->_class;
        $ext = false;
        $data = [
            'vars' => $vars,
            'context' => $context,
            'engine' => false
        ];

        // compiled?
        if ($this->_http->app->pluginExists('compiled') && ($v = $this->_http->app['compiled']->get('views')) != false) {
            foreach ($this->_dirs as $dir) {
                $_ = b::path($dir, $file);
                if (array_key_exists($_, $v['data']['map'])) {
                    $class = 'bolt\http\views\compiled';
                    $data['compiled'] = require( $this->_http->app['compiled']->getFilePath("views/{$v['data']['map'][$_]['name']}") );
                    $data['engine'] = $this->_http->app['render']->getEngine($v['data']['map'][$_]['ext']);
                }
            }
        }
        else if (!file_exists($file) && !($file = $this->find($file))) {
            throw new Exception("Unable to find view '$file'.");
        }
        else {
            $data['file'] = $file;
            $data['engine'] = $this->_http->app['render']->getEngine(pathinfo($file)['extension']);
        }

        // make sure we have an engine for this ext
        if (!$data['engine']) {
            throw new Exception("Unable to find render engine for '$ext'.");
        }

        // create our view
        return new $class($this, $data);

    }

    public function compile($e) {
        $vdir = $e->data['client']->makeDir("views");

        $map = [];

        $dirs = [];

        // loop through all directories and find
        // files that we can compile
        foreach ($this->_dirs as $dir) {
            $root = $this->_http->path($dir);
            $dirs[$dir] = array_merge(b::fs('glob', $root."/**/*.*")->asArray(), b::fs('glob', $root."/*.*")->asArray() );
        }

        foreach ($dirs as $root => $files) {
            foreach ($files as $file) {
                $ext = pathinfo($file)['extension'];
                $rel = str_replace($this->_http->app->getRoot(), '', $file);
                if ($this->_http->app['render']->hasEngine($ext, true)) {
                    $id = md5($rel);
                    $map[$rel] = [
                        'modified' => filemtime($file),
                        'dir' => $dir,
                        'name' => "{$id}.php",
                        'ext' => $ext
                    ];
                    $var = $this->_http->app['render']->getEngine($ext)->compile( file_get_contents($file));
                    file_put_contents("{$vdir}/{$id}.php", '<?php return '.var_export($var, true).';');
                }
            }
        }


        $e->data['client']->saveCompileLoader('views', ['map' => $map]);


    }

}
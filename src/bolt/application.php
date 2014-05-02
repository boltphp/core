<?php

namespace bolt;
use \b;



/**
 * Base bolt applicatin class
 */
class application extends plugin {
    use helpers\events; /// use events class

    /**
     * root application path
     *
     * @var string
     */
    private $_root = false;

    /**
     * has this application run yet
     *
     * @var bool
     */
    private $_hasRun = false;

    /**
     * classname based autoloading
     *
     * @var array
     */
    private $_autoload = [];

    /**
     * root directory of bootstrap files
     *
     * @var string
     */
    private $_bootstrapDir = false;


    /**
     * construct a new application instance
     *
     * @param $config array config
     *
     * @return self
     */
    public function __construct($config=[]) {
        $this->_root = b::path(isset($config['root']) ? realpath($config['root']) : getcwd());

        // autoload
        $this->_autoload = b::param('autoload', [], $config);

        if (isset($config['bootstrap'])) {
            $this->bootstrap($config['bootstrap']);
            $this->_bootstrapDir = $this->path($config['bootstrap']);
        }

        if (isset($config['compiled'])) {
            $this->loadCompiled($config['compiled']);
        }

        if (isset($config['plugins'])) {
            $this->plug($config['plugins']);
        }

        // register
        spl_autoload_register([$this, 'autoload']);

    }


    /**
     * autoload
     *
     * @param string $class
     *
     * @return void
     */
    public function autoload($class) {
        foreach ($this->_autoload as $prefix => $base) {
            if (is_string($prefix)) {
                $l = strlen($prefix);
                if (substr($class,0,$l) == $prefix) {
                    $class = str_replace($prefix, '', $class);
                }
                else {continue;}
            }
            $file = str_replace('\\', DIRECTORY_SEPARATOR, $class);
            $_ = b::path($base, $file).".php";
            if (file_exists($_)) {
                require_once($_);
            }
        }
    }

    /**
     * getBootsrapDir
     *
     * @return string
     */
    public function getBootstrapDir() {
        return $this->_bootstrapDir;
    }

    /**
     * load boostrap files
     *
     * @param string $what file or dir path, absolute or relative to $root
     *
     * @return self
     */
    public function bootstrap($what) {
        if (is_dir($what) || is_dir($this->path($what))) {
            $path = is_dir($what) ? $what : $this->path($what);
            foreach (b::getRegexFiles($path) as $file) {
                $this->bootstrap($file);
            }
            return $this;
        }
        else if (!is_file($what) && is_file($this->path($what))) {
            $this->bootstrap($this->path($what));
        }
        else if (!is_file($what)) {
            throw new \Exception("Unable to load bootstrap $what");
        }

        // load it
        $resp = require_once($what);

        if (is_callable($resp)) {
            call_user_func($resp, $this);
        }
        else if (is_object($resp) && method_exists($resp, 'bootstrap')) {
            call_user_func([$resp, 'bootstrap'], $this);
        }
        else if (is_string($resp) && class_exists($resp)) {
            $o = new $resp($this);
        }

        return $this;
    }


    /**
     * get a compiled object is the compiled
     * plugin exists
     *
     * @param  string $name
     *
     * @return array
     */
    public function getCompiled($name) {
        return $this->pluginExists('compiled') ? $this['compiled']->get($name) : [];
    }

    /**
     * return all autoload settings
     *
     * @return array
     */
    public function getAutoload() {
        return $this->_autoload;
    }


    /**
     * check env and run callback if in that env
     *
     * @param string $env
     * @param closure $callback
     *
     * @return self
     */
    public function env($env, \Closure $callback) {
        if (b::env() === $env) {
            call_user_func($callback->bindTo($this));
        }
        return $this;
    }


    /**
     * has the application run yet
     *
     * @return bool
     */
    public function hasRun() {
        return $this->_hasRun;
    }

    /**
     * get the root path
     *
     * @return string
     */
    public function getRoot() {
        return $this->_root;
    }


    /**
     * set the root path for the app
     *
     * @param string $root new root path
     *
     * @return self
     */
    public function setRoot($root){
        $this->_root = b::path($root);
        return $this;
    }


    /**
     * get a path relative to the $root
     *
     * @param ... path parts
     *
     * @return string
     */
    public function path() {
        return b::path($this->_root, call_user_func_array(['b', 'path'], func_get_args()));
    }


    /**
     * add a ns to the class loader
     *
     * @param string $ns namespace of class
     * @param string $path path of classes relative to $root
     *
     * @return self
     */
    public function load($ns, $path) {

        b::requireFromPath( $this->path($path) );


    }


    /**
     * get composer file
     */
    public function getComposerFile() {
        $composer = b::path($this->_root, "composer.json");

        while(file_exists($composer) === false && $composer !== '/composer.json') {
            $composer = b::path(realpath(dirname($composer)."/../"), 'composer.json');
        }

        if (file_exists($composer)) {
            return [
                'dir' => dirname($composer),
                'file' => json_decode(file_get_contents($composer))
            ];
        }

        return null;
    }

    /**
     * run the application
     *
     * @return void
     */
    public function run() {

        // if we're in a cli, run it
        if (php_sapi_name() === 'cli') {
            $this->fire('run:cli');
        }

        // else assume a http
        else {
            $this->fire('run:http');
        }


        $this->_hasRun = true;

    }

}
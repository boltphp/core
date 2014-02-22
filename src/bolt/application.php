<?php

namespace bolt;
use \b;



/**
 * Base bolt applicatin class
 *
 */
class application extends plugin {
    use events; /// use events class


    /**
     * @var string
     */
    private $_root = false;

    /**
     * @var bool
     */
    private $_hasRun = false;

    /**
     * @var array
     */
    private $_autoload = [];

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
     * run the application
     *
     * @return void
     */
    public function run() {


        // fire any run events
        $this->fire('run');


        $this->_hasRun = true;

    }

}
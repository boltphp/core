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
     * construct a new application instance
     *
     * @param $config array config
     *
     * @return self
     */
    public function __construct($config=[]) {

        $this->_root = b::path(isset($config['root']) ? realpath($config['root']) : getcwd());

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
     * @params ... path parts
     *
     * @return string
     */
    public function path() {
        return b::path($this->_root, call_user_func_array([b::instance(), 'path'], func_get_args()));
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
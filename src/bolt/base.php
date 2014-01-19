<?php

namespace bolt;
use \b;


use \Closure;

// symfony
use Symfony\Component\ClassLoader\ClassLoader;

class base implements \ArrayAccess {
    use plugin;
    use plugin\arrayAccess;
    use plugin\helpers;

    /**
     * our environment settings
     * @var
     */
    private $_env = 'dev';

    /**
     * callbacks for initating modes
     * @var
     */
    private $_modes = [];

    /**
     * root path
     * @var
     */
    private $_root = "";

    /**
     * construct a new base boject
     */
    public function __construct($config) {

        // add our internal helper class
        $this->helper('\bolt\helpers');

        $this->_root = isset($config['root']) ? $config['root'] : false;
        $this->env( isset($config['env']) ? $config['env'] : 'dev' );

        // loader
        $this->loader = new ClassLoader();
        $this->loader->setUseIncludePath(true);
    }

    /**
     * set the environment
     *
     * @param string $env env name
     * @return string env
     */
    public function env($env=null) {
        if($env) {$this->_env = $env;}
        return $this->_env;
    }

    /**
     * add initalize callback for a mode
     *
     * @param string $mode mode name or class
     * @param Closure $callback function to execute after initializing mode
     * @param array $config configuration passed to mode
     *
     * @return self
     */
    public function mode($mode, Closure $callback, $config=[]) {
        $this->_modes[$mode] = [
            'class' => '\bolt\browser',
            'callback' => $callback,
            'config' => $config
        ];
        return $this;
    }

    /**
     * run the mode
     *
     * @return void
     */
    public function run() {
        $func = $this->_modes['browser'];
        $ctx = $func['class']::mode($this, $func['config']);
        call_user_func($func['callback']->bindTo($ctx, $ctx));
        $ctx->run();
    }

    /**
     * load php files with a prefix
     *
     * @param string $prefix path prefix
     * @param string $path path
     *
     * @return self
     */
    public function load($class, $path) {

        if (is_array($class)) {
            array_walk($class, function($opt){
                call_user_func_array([$this, 'load'], $opt);
            });
            return $this;
        }

        b::requireFromPath($path);

//        b::load($class, $path);

        return $this;


        // $this->loader->addPrefix($prefix, $path);
        // $this->loader->register();
        // return $this;
    }

    /**
     * get a path relative to $root
     *
     * @param $path
     *
     * @return string path relative to $root
     */
    public function path($path) {
        return b::path($this->_root, $path);
    }

}
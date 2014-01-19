<?php

namespace bolt;
use \b;

use \RecursiveDirectoryIterator;
use \RecursiveIteratorIterator;
use \RecursiveRegexIterator;
use \RegexIterator;
use \Closure;

// symfony
use Symfony\Component\ClassLoader\ClassLoader;

class base implements \ArrayAccess {
    use plugin;
    use plugin\arrayAccess;
    use plugin\helpers;

    /**
     * our environment settings
     */
    private $_env = 'dev';

    /**
     * callbacks for initating modes
     */
    private $_modes = [];

    /**
     * construct a new base boject
     */
    public function __construct() {
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
    public function load($prefix, $path) {
        $this->loader->addPrefix($prefix, $path);
        $this->loader->register();
        return $this;
    }

}
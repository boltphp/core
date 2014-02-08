<?php

namespace bolt;
use \b;

class application extends plugin {
    use events;

    private $_root = false;

    /**
     * construct a new application instance
     *
     * @param $config array config
     *
     * @return self
     */
    public function __construct($config) {

        $this->_root = isset($config['root']) ? realpath($config['root']) : getcwd();

    }

    public function load($ns, $root) {
        $rootPath = b::path($this->_root, $root);

        b::requireFromPath($rootPath);


    }

    public function path() {

    }

    public function run() {

        // fire any run events
        $this->fire('run');

    }

}
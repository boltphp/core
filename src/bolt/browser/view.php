<?php

namespace bolt\browser;

class view {

    private $_app;

    private $_dirs = [];
    private $_layouts = [];

    private $_engines = [];

    public function __construct(\bolt\application $app, $config) {

        $this->_app = $app;

        $this->_dirs = isset($config['dirs']) ? (array)$config['dirs'] : [];
        $this->_layouts = isset($config['layouts']) ? (array)$config['layouts'] : [];


    }

    public function __invoke() {

    }

    public function make() {

    }

    public function engine($ext, $class) {
        $this->_engines[$ext] = $class;
    }

    public function getLayout($path) {

        // find this file
        $file = $this->find($this->_layouts, $path);

        var_dump($file); die;

    }

    public function find($dirs, $path) {
        foreach ($dirs as $dir) {
            $_ = $this->_app->path($dir, $path);

        }

    }

}
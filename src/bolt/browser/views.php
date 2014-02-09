<?php

namespace bolt\browser;

use \Exception;

class views {

    private $_dirs = [];
    private $_layouts = [];

    private $_engines = [];

    private $_class = 'bolt\browser\views\view';

    public function __construct(\bolt\browser $browser, $config) {

        $this->_browser = $browser;

        $this->_dirs = isset($config['dirs']) ? (array)$config['dirs'] : [];
        $this->_layouts = isset($config['layouts']) ? (array)$config['layouts'] : [];

        if (isset($config['engines'])) {
            foreach ($config['engines'] as $engine) {
                $this->engine($engine[0], $engine[1]);
            }
        }

    }


    public function engine($ext, $class) {
        $this->_engines[$ext] = [
            'class' => $class,
            'instance' => false
        ];
        return $this;
    }

    public function find($file) {
        foreach ($this->_dirs as $dir) {
            $_ = $this->_browser->path($dir, $file);
            if (file_exists($_)){
                return $_;
            }
        }
        return false;
    }

    public function create($file, $vars = [], $context = false) {
        $file = $this->find($file);

        if (!$file) {
            throw new Exception("Unable to find view '$file'.");
            return;
        }

        // ext
        $ext = strtolower(pathinfo($file)['extension']);

        // need an engine
        if (!array_key_exists($ext, $this->_engines)) {
            throw new Exception("Unable to find render engine for '$ext'.");
            return false;
        }

        $engine = $this->_engines[$ext];

        // no instance
        if (!$engine['instance']) {
            $engine['instance'] = $this->_engines[$ext]['instance'] = new $engine['class'];
        }

        // create our view
        return new $this->_class($this, $file, $engine['instance'], ['vars' => $vars, 'context' => $context ]);

    }

}
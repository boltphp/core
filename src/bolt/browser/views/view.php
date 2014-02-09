<?php

namespace bolt\browser\views;
use \b;

class view implements face {

    private $_manager;

    private $_file;

    private $_vars;

    private $_context;

    private $_engine;

    public function __construct(\bolt\browser\views $manager, $file, $engine, $config = []) {

        $this->_manager = $manager;

        $this->_file = $file;

        $this->_engine = $engine;

        $this->_vars = $config['vars'];

        $this->_context = $config['context'];

    }


    public function render() {
        $str = file_get_contents($this->_file);

        return $this->_engine->render($str, $this->_vars);
    }

    public function __invoke() {
        return $this->render();
    }

    public function __toString() {
        return (string)$this->render();
    }

}
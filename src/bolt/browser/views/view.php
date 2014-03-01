<?php

namespace bolt\browser\views;
use \b;

class view implements face {

    private $_manager;

    private $_file;

    private $_vars;

    private $_context;

    public function __construct(\bolt\browser\views $manager, $file, $config = []) {

        $this->_manager = $manager;

        $this->_file = $file;

        $this->_vars = $config['vars'];

        $this->_context = $config['context'];

    }

    public function render() {
        $this->_vars['context'] = $this->_context;
        return $this->_manager->renderFile($this->_file, $this->_vars);
    }

    public function __invoke() {
        return $this->render();
    }

    public function __toString() {
        return (string)$this->render();
    }

}
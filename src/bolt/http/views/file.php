<?php

namespace bolt\http\views;
use \b;

class file implements face {

    private $_manager;

    private $_file;

    private $_vars;

    private $_context;

    public function __construct(\bolt\http\views $manager, $config = []) {

        $this->_manager = $manager;

        $this->_file = $config['file'];

        $this->_engine = $config['engine'];

        $this->_vars = $config['vars'];

        $this->_context = $config['context'];

    }

    public function render() {
        $this->_vars['context'] = $this->_context;
        return $this->_engine->renderFile($this->_file, $this->_vars);
    }

    public function __invoke() {
        return $this->render();
    }

    public function __toString() {
        return (string)$this->render();
    }

}
<?php

namespace bolt\browser\views;
use \b;

class compiled implements face {

    private $_manager;

    private $_compiled;

    private $_vars;

    private $_engine;

    private $_context;

    public function __construct(\bolt\browser\views $manager, $config = []) {

        $this->_manager = $manager;

        $this->_compiled = $config['compiled'];

        $this->_engine = $config['engine'];

        $this->_vars = $config['vars'];

        $this->_context = $config['context'];

    }

    public function render() {
        $this->_vars['context'] = $this->_context;
        return $this->_engine->renderCompiled($this->_compiled, $this->_vars);
    }

    public function __invoke() {
        return $this->render();
    }

    public function __toString() {
        return (string)$this->render();
    }

}
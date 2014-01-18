<?php

namespace bolt\browser;
use \b;

class view {

    private $_file;
    private $_vars;
    private $_context;

    private $_before = [];
    private $_after = [];

    public function __construct($config=[]) {
        $this->_file = b::param('file', false, $config);
        $this->_vars = b::param('vars', [], $config);
        $this->_context = b::param('context', false, $config);
    }

    public function before() {

    }

    public function after() {

    }

    public function render() {

        // render
        $str = b::render('file', $this->_file, [
                'context' => $this->_context,
                'vars' => $this->_vars
            ]);

        return $str;

    }

    public function __invoke() {
        return $this->render();
    }

    public function __toString() {
        return $this->render();
    }

}
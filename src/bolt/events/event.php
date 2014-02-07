<?php

namespace bolt\events;
use \b;

class event {

    private $_parent;
    private $_data = [];
    private $_listener;

    public function __construct($parent, \bolt\events\listener $listener, $data) {
        $this->_parent = $parent;
        $this->_listener = $listener;
        $this->_data = $data;
    }

    public function getParent() {
        return $this->_parent;
    }

    public function getData() {
        return $this->_data;
    }

    public function getListener() {
        return $this->_listener;
    }

    public function getArguments() {
        return $this->_listener->getArguments();
    }

}
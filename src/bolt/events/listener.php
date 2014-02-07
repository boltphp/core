<?php

namespace bolt\events;
use \b;


class listener {

    private $_guid;

    private $_parent;

    private $_listener;

    private $_args;

    public function __construct($parent, $listener, $args) {
        $this->_guid = b::guid('event');
        $this->_parent = $parent;
        $this->_listener = $listener;
        $this->_args = $args;
    }

    public function detach() {
        $this->_parent->off($this);
    }

    public function execute($e) {
        call_user_func($this->_listener, $e);
    }

}
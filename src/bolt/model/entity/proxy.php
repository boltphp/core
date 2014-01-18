<?php

namespace bolt\model\entity;
use \b;

class proxy {

    private $_manager;
    private $_class;

    public function __construct(\bolt\model\manager $manager, $class) {
        $this->_manager = $manager;
        $this->_class = $class;
    }

    public function __call($name, $args) {
        array_unshift($args, $this->_class);
        return call_user_func_array([$this->_manager, $name], $args);
    }


}
<?php

namespace bolt;

class source implements plugin\singleton, \ArrayAccess {

    private $_instance = [];

    public function __construct($config=null) {
        if (is_array($config)) {
            $this->add('default', $config);
        }
    }

    public function __get($name) {
        if (array_key_exists($name, $this->_instance)) {
            return $this->_instance[$name];
        }
        return false;
    }

    public function add($name, $config) {
        $adapter = $config['adapter'];
        $this->_instance[$name] = new $adapter($config);
        return $this;
    }

    public function __call($name, $args) {
        if (array_key_exists('default', $this->_instance)) {
            return call_user_func_array([$this->_instance['default'], $name], $args);
        }
    }

    public function offsetSet($name, $adapter) {
        $this->_instance[$name] = $adapter;
    }

    public function offsetExists($name) {
        return array_key_exists($name, $this->_instance);
    }

    public function offsetUnset($name) {
        if (array_key_exists($name, $this->_instance)) {
            $this->_instance->__destruct();
            unset($this->_instance[$name]);
        }
        return true;
    }

    public function offsetGet($name) {
        if (array_key_exists($name, $this->_instance)) {
            return $this->_instance[$name];
        }
        return false;
    }

}
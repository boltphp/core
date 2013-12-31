<?php

namespace bolt\bucket;
use \b;

use Stringy\Stringy;


class s implements face {

    private $_str = false;

    public function __construct($value, $encode='UTF-8') {
        $this->_str = new Stringy($value, $encode);
    }

    public function __get($name) {
        if ($name == 'value') {
            return $this->normalize();
        }
    }

    public function __call($name, $args) {
        if (method_exists($this->_str, $name)){
            return call_user_func_array([$this->_str, $name], $args);
        }
        return false;
    }

    public function set($value) {
        return $this->_str = new Stringy($value, 'UTF-8');
    }

    public function get() {
        return $this;
    }

    public function value() {
        return $this->normalize();
    }

    public function normalize() {
        return (string)$this->_str;
    }

    public function __toString() {
        return $this->normalize();
    }

}
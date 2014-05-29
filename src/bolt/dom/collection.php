<?php

namespace bolt\dom;


class collection extends \bolt\helpers\collection {

    private $_document;

    public function __construct(document $document) {
        $this->_document = $document;
    }

    public function __get($name) {
        return $this->_passToFirst('__get', [$name]);
    }

    public function __set($name, $value) {
        return $this->_passToFirst('__set', [$name, $value]);
    }

    public function __call($name, $args) {
        return $this->_passToFirst($name, $args);
    }

    private function _passToFirst($func, $args) {
        if (count($this) == 1) {
            return call_user_func_array([$this->first(), $func], $args);
        }
        return null;
    }

}
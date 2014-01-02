<?php

namespace bolt\bucket;
use \b;


class a implements face, \IteratorAggregate, \Countable {

    private $_access = false;

    private $_value = [];

    public function __construct($value=[]) {
        $this->_access = b::bucket("access");
        $this->_value = $value;
    }

    public function __get($name) {
        if ($name === 'value') {
            return $this->normalize();
        }
    }

    public function get($key, $default=null) {
        if (!is_numeric($key)) {
            $key = '['.trim(str_replace('.', '][', $key), '[]').']';
        }
        $value = $this->_access->getValue($this->_value, $key);
        return b::bucket('create', $value ?: $default);
    }

    public function set($key, $value) {
        if (!is_numeric($key)) {
            $key = '['.trim(str_replace('.', '][', $key), '[]').']';
        }
        $this->_access->setValue($this->_value, $key, $value);
        return $this;
    }

    public function normalize() {
        return $this->_value;
    }

    public function value($key, $default=null) {
        return $this->get($key, $default)->normalize();
    }

    public function getIterator() {
        return new \ArrayIterator($this->_value);
    }


    /**
     * @brief get count of data
     *
     * @return count
     */
    function count() {
        return count($this->_value);
    }



}

<?php

namespace bolt\bucket;


class b implements face {

    private $_value = null;

    public function __construct($value=null) {
        $this->_value = $value;
    }

    public function normalize() {
        return $this->_value;
    }

    public function __toString() {
        return (string)$this->normalize();
    }

}
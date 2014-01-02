<?php

namespace bolt\bucket;

class n implements face {

    public $value = null;

    public function get() {
        return $this;
    }

    public function value() {
        return $this->normalize();
    }

    public function normalize() {
        return null;
    }

}
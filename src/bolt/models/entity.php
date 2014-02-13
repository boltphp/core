<?php

namespace bolt\models;

abstract class entity {

    private $_manager;

    private $_loaded = false;

    public function setManager(\bolt\models $manager) {
        $this->_manager = $manager;
        return $this;
    }

    public function setLoaded($loaded) {
        $this->_loaded = $loaded;
        return $this;
    }

    public function isLoaded() {
        return $this->_loaded;
    }

    public function loaded() {
        return $this->_loaded;
    }

    public function __get($name){
        return $this->{$name};
    }

}
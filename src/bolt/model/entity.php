<?php

namespace bolt\model;

class entity {

    private $_manager = false;

    private $_loaded = false;

    public function loaded() {
        return $_loaded;
    }
    public function setLoaded($loaded) {
        $this->_loaded = $loaded;
        return $this;
    }


    public function setManager($manager) {
        $this->_manager = $manager;
    }

    public function save() {

    }

    public function delete() {

    }

}
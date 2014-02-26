<?php

namespace bolt\models;


/**
 *
 * @todo implement interface to make sure
 *          users are implementing the struct
 *          method
 */
abstract class entity {

    private $_manager;

    private $_loaded = false;

    final public function setManager(\bolt\models $manager) {
        $this->_manager = $manager;
        return $this;
    }

    final public function getManager() {
        return $this->_manager;
    }

    final public function setLoaded($loaded) {
        $this->_loaded = $loaded;
        return $this;
    }

    final public function isLoaded() {
        return $this->_loaded;
    }

    final public function loaded() {
        return $this->_loaded;
    }

    private function _hasOp($op, $name) {
        if (stripos($name, '_') !== false) {
            $name = implode("", array_map(function($part){
                return ucfirst($part);
            }, explode("_", $name)));
        }
        $func = $op.strtoupper($name)."Attr";
        return method_exists($this, $func) ? $func : false;
    }

    public function __get($name){
        if (($op = $this->_hasOp('get', $name)) !== false) {
            return call_user_func([$this, $op]);
        }
        return $this->{$name};
    }

    public function __set($name, $value) {
        if (($op = $this->_hasOp('set', $name)) !== false) {
            return call_user_func([$this, $op]);
        }
        return $this->{$name} = $value;
    }

}
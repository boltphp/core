<?php

namespace bolt\models;


/**
 *
 * @todo implement interface to make sure
 *          users are implementing the struct
 *          method
 */
abstract class entity {

    /**
     * models manager
     *
     * @var bolt\models
     */
    private $_manager;

    /**
     * is this model loaded
     *
     * @var bool
     */
    private $_loaded = false;


    /**
     * set the models manager
     *
     * @param bolt\models $manager
     *
     * @return self
     */
    final public function setManager(\bolt\models $manager) {
        $this->_manager = $manager;
        return $this;
    }


    /**
     * get the models manager
     *
     * @return bolt\models
     */
    final public function getManager() {
        return $this->_manager;
    }


    /**
     * set if the model is loaded
     *
     * @param bool $loaded
     *
     * @return self
     */
    final public function setLoaded($loaded) {
        if (!is_bool($loaded)) {
            throw new \Exception("Set loaded value must be a bool. $loaded provided");
        }
        $this->_loaded = $loaded;
        return $this;
    }


    /**
     * check if the model is loaded
     *
     * @return bool
     */
    final public function isLoaded() {
        return $this->_loaded;
    }


    /**
     * check if the model is loaded
     *
     * @return bool
     */
    final public function loaded() {
        return $this->_loaded;
    }


    /**
     * does this entity have an attribute oporator
     *
     * @param string $op name of oporator (get|set)
     * @param string $name name of attribute
     *
     * @return mixed
     */
    private function _hasOp($op, $name) {
        if (stripos($name, '_') !== false) {
            $name = implode("", array_map(function($part){
                return ucfirst($part);
            }, explode("_", $name)));
        }
        $func = $op.strtoupper($name)."Attr";
        return method_exists($this, $func) ? $func : false;
    }


    /**
     * get an attribute value
     *
     * @return mixed
     */
    public function __get($name){
        if (($op = $this->_hasOp('get', $name)) !== false) {
            return call_user_func([$this, $op]);
        }
        return $this->{$name};
    }


    /**
     * set an attribute value
     *
     * @param string $name
     * @param mixed $value
     *
     * @return mixed
     */
    public function __set($name, $value) {
        if (($op = $this->_hasOp('set', $name)) !== false) {
            return call_user_func([$this, $op], $value);
        }
        return $this->{$name} = $value;
    }

}
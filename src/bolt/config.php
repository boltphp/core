<?php

namespace bolt;
use \b;

use Symfony\Component\PropertyAccess\PropertyAccess;


class config implements \IteratorAggregate, \ArrayAccess {

    private $_app;

    private $_storage = [];

    private $_access = [];

    public function __construct(\bolt\application $app, $config = []) {
        $this->_app = $app;
        $this->_access = PropertyAccess::createPropertyAccessorBuilder()
                ->disableExceptionOnInvalidIndex()
                ->getPropertyAccessor();
        if (isset($config['register'])) {
            $this->register($config['register']);
        }
    }

    public function register($name, $file = []) {
        if (is_array($name)) {
            foreach ($name as $item) {
                call_user_func_array([$this, 'register'], $item);
            }
            return $this;
        }

        $data = $this->_readFile($file);

        // if there's an _$env we need to merge it
        // into the base
        $env = b::env();
        if (isset($data["_{$env}"])) {
            $data = b::mergeArray($data, $data["_{$env}"]);
        }

        $this->_storage[$name] = $data;
        return $this;
    }


    public function _readFile($file) {
        $path = $this->_app->path($file);
        $ext = strtolower(pathinfo($path)['extension']);

        switch($ext) {
            case 'json':
                return json_decode(file_get_contents($path), true);

        };

        return false;

    }

    public function __get($name) {
        return array_key_exists($name, $this->_storage) ? $this->_storage : false;
    }

    public function get($name, $default = null) {
        return $this->_access->getValue($this->_storage, $this->_parseName($name)) ?: $default;
    }

    public function set($name, $value) {
        $this->_access->setValue($this, $this->_parseName($name), $value);
    }

    private function _parseName($str) {
        return implode("", array_map(function($val){
            return "[{$val}]";
        }, explode(".", $str)));
    }


    public function getIterator() {
        return new \ArrayIterator($this->_storage);
    }

    /**
     * set offset
     *
     * @param string $name set the name
     * @param string $class class name
     *
     * @return self
     */
    public function offsetSet($name, $value) {
        return $this->register($name, $value);
    }


    /**
     * offset get
     *
     * @param string $name name of plugin
     *
     * @return bool
     */
    public function offsetExists($name) {
        return $this->exists($name);
    }


    /**
     * unplug
     *
     * @param string $name name of plugin to unplug
     *
     * @return void
     */
    public function offsetUnset($name) {
        return $this->remove($name);
    }


    /**
     * get a plugin
     *
     * @param string $name name of plugin
     *
     * @return mixed
     */
    public function offsetGet($name) {
        return $this->get($name);
    }


}
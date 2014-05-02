<?php

namespace bolt;
use \b;

use Symfony\Component\PropertyAccess\PropertyAccess;


/**
 * Base configuration manager
 */
class config implements \IteratorAggregate, \ArrayAccess {

    /**
     * @var bolt\application
     */
    private $_app;

    /**
     * @var array
     */
    private $_storage = [];

    /**
     * @var Symfony\Component\PropertyAccess\PropertyAccess
     */
    private $_access = [];


    private $_config = [];

    private $_compiled = [];

    /**
     * Constructor
     *
     * @param bolt\application $app
     * @param array $config
     *
     */
    public function __construct(\bolt\application $app, $config = []) {
        $this->_app = $app;
        $this->_access = PropertyAccess::createPropertyAccessorBuilder()
                ->disableExceptionOnInvalidIndex()
                ->getPropertyAccessor();


        $this->_config = $config;

        // check for compiled files
        $this->_compiled = $app->getCompiled('config');


        if (isset($config['register'])) {
            $this->register($config['register']);
        }

        $app->on("compile", [$this, 'onCompile']);

    }

    public function onCompile($e) {
        $dirs = isset($this->_config['dirs']) ? $this->_config['dirs'] : [];

        if (count($dirs) == 0) {return false;}

        $map = [];

        foreach ($dirs as $dir) {
            $path = $this->_app->path($dir);

            // find any files that we a
            $files = array_merge(iterator_to_array(b::fs("glob", "{$path}/*.json")), iterator_to_array(b::fs("glob", "{$path}/**/*.json")));

            foreach ($files as $file) {
                $rel = b::path(str_replace($path, $dir, $file->getRealPath()));
                $map[$rel] = $this->_readFile($file->getRealPath());
            }

        }

        if (count($map) == 0) {return;}

        // write to compiled
        $e->data['client']->saveCompileLoader('config', $map);

    }


    /**
     * register one or more config namspaces
     *
     * @param string|array $name namespace or array of register items
     * @param string|array $data config data or file
     *
     * @return self
     */
    public function register($name, $data = []) {
        if (is_array($name)) {
            foreach ($name as $item) {
                call_user_func_array([$this, 'register'], $item);
            }
            return $this;
        }

        if (is_string($data) && isset($this->_compiled['data'][b::path($data)])) {
            $data = $this->_compiled['data'][b::path($data)];
        }
        else if (is_string($data)) {
            $data = $this->_readFile($this->_app->path($data));
        }

        // if there's an _$env we need to merge it
        // into the base
        $env = b::env();
        if (isset($data["_{$env}"])) {
            $data = b::mergeArray($data, $data["_{$env}"]);
        }

        $this->_storage[$name] = $data;
        return $this;
    }


    /**
     * return all registered namespaces
     *
     * @return array
     */
    public function getRegistered() {
        return $this->_storage;
    }


    /**
     * read a file and parse the data
     *
     * @param string $path file path
     *
     * @return array
     */
    protected function _readFile($path) {
        if (!file_exists($path)) {
            throw new \Exception("No file to read");
        }

        $ext = strtolower(pathinfo($path)['extension']);
        switch($ext) {
            case 'json':
                return json_decode(file_get_contents($path), true);

        };

        return false;

    }


    /**
     * magic get a namespace
     *
     * @param string $name namespace
     *
     * @return mixed
     */
    public function __get($name) {
        return array_key_exists($name, $this->_storage) ? $this->_storage[$name] : false;
    }


    public function has($name) {
        return $this->get($name, -99) !== -99;
    }

    /**
     * get a value from a stored namespace
     *
     * @param string $name namespace or key
     * @param mixed $default value to return if $name ne
     *
     * @return mixed
     */
    public function get($name, $default = null) {
        return $this->_access->getValue($this->_storage, $this->_parseName($name)) ?: $default;
    }


    /**
     * set a value on a namespace
     *
     * @param string $name
     * @param mixed $value
     *
     * @return self
     */
    public function set($name, $value) {
        $this->_access->setValue($this->_storage, $this->_parseName($name), $value);
        return $this;
    }


    /**
     * remove a namespace
     *
     * @param string $name
     *
     * @return self
     */
    public function remove($name) {
        unset($this->_storage[$name]);
        return $this;
    }


    /**
     * does a key exist
     *
     * @param string $name
     *
     * @return bool
     */
    public function exists($name) {
        return $this->get($name, -1) !== -1;
    }

    /**
     * parse a name and convert to array accesstor format
     *
     * @param string $str key name
     *
     * @return string
     */
    private function _parseName($str) {
        return implode("", array_map(function($val){
            return "[{$val}]";
        }, explode(".", $str)));
    }


    /**
     * return an array iterator for storage items
     *
     * @return ArrayIterator
     */
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
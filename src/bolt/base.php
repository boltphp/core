<?php

namespace bolt;
use \b;

use \RecursiveDirectoryIterator;
use \RecursiveIteratorIterator;
use \RecursiveRegexIterator;
use \RegexIterator;

// symfony
use Symfony\Component\ClassLoader\ClassLoader;

class base {

    private $_helpers = [];
    private $_plugins = [];

    private $_settings = false;

    public function __construct() {
        $this->loader = new ClassLoader();
        $this->loader->setUseIncludePath(true);
    }

    // init
    public function init($config) {

        // add our internal helper class
        $this->helper('\bolt\helpers');

    }

    /**
     * call method dispatches calls to bolt, plugins or helpers
     * supported syntax
     *     b::path()    - helper method bolt\helpers::path (on helper class instance)
     *     b::browser('response\create') - bolt\browser\response::create (static method create)
     *     b::fs('finder') - new bolt\browser\fs\finder (b/c it implements bolt\plugin\factory)
     *
     * @param $name <string> name of method called. understores = namespace
     * @param $args <array> array of arguments to pass to function
     *          - args[0] = name of method to call on sub class for plugin
     *              or function calles. \ used to namespace
     *
     *
     * @return <mixed> result of function call or false
     */
    public function call($name, $args=[]) {

        // see if we have this function on base
        if (method_exists($this, $name)) {
            return call_user_func_array([$this, $name], $args);
        }

        // see if we have a plugin name $name
        if (array_key_exists($name, $this->_plugins)) {
            $p = $this->_plugins[$name];

            // see if this plugin is a singleton or factory
            if ($p['type'] == 'singleton') {
                if (!$p['instance'] AND $p['ref']->hasMethod('instance')) {
                    $class = $this->_plugins[$name]['class'];
                    $p['instance'] = $class::instance();
                }
                else if (!$p['instance']) {
                    $p['instance'] = $this->_plugins[$name]['instance'] = $p['ref']->newInstance();
                }

                if (count($args) === 0) {
                    return $p['instance'];
                }
                else if ($p['ref']->hasMethod('dispatch')) {
                    return call_user_func_array([$p['instance'], 'dispatch'], $args);
                }
                else if (isset($args[0]) AND $p['ref']->hasMethod($args[0])) {
                    $method = array_shift($args);
                    return call_user_func_array([$p['instance'], $method], $args);
                }
                else {
                    return $p['instance'];
                }

            }
            else {
                return $p['ref']->newInstanceArgs($args);
            }

        }

        // helper class
        if (count($this->_helpers)) {
            foreach ($this->_helpers as $helper) {

                if (in_array($name, $helper['methods'])) {

                    if (!$helper['instance']) {
                        $this->_helpers[$helper['name']]['instance'] = $helper['ref']->newInstance();
                    }

                    return call_user_func_array([$this->_helpers[$helper['name']]['instance'], $name], $args);
                }
            }
        }


        // name has a / in it
        if (isset($args[0]) AND stripos($args[0], '\\') !== false) {
            $parts = explode('\\', array_shift($args));
            array_unshift($args, array_pop($parts));
            array_unshift($parts, $name);
            $name = implode("_", $parts);
        }

        // is it a bolt class?
        $class = '\bolt\\'.str_replace("_", '\\', $name);

        // first of the args should be a function name
        $func = array_shift($args);

        // the function with the named args
        if (method_exists($class, $func)) {
            return call_user_func_array(array($class, $func), $args);
        }

        return false;

    }


    public function helper($class) {
        $ref = new \ReflectionClass($class);
        $methods = [];

        foreach ($ref->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            $methods[] = $method->name;
        }

        $this->_helpers[$ref->name] = [
            'name' => $ref->name,
            'ref' => $ref,
            'methods' => $methods,
            'instance' => false
        ];

        return $this;
    }

    public function plug($name, $class) {
        $ref = new \ReflectionClass($class);

        // add to plugins
        $this->_plugins[$name] = [
            'name' => $name,
            'type' => $ref->isSubclassOf('bolt\plugin\singleton') ? 'singleton' : 'factory',
            'ref' => $ref,
            'instance' => false
        ];

        return $this;

    }


    public function load($prefix, $path) {
        $this->loader->addPrefix($prefix, $path);
        $this->loader->register();
        return $this;
    }

    public function settings($name=null, $value=null) {
        if (!$this->_settings) { $this->_settings = new bucket\a(); }
        if ($name === null AND $value === null) {
            return $this->_settings;
        }
        if ($value === null) {
            return $this->_settings->get($name);
        }
        else {
            $this->_settings->set($name, $value);
        }
        return $this;
    }


}
<?php

namespace bolt;
use \b;

trait plugin {

    private $_plugins = [];
    private $_parents = [];

    public $isPlugable = true;

    public function inherit($parent) {
        $this->_parents[] = $parent;
        return $this;
    }

    public function plug($name, $class, $config=[]) {
        $ref = new \ReflectionClass($class);

        // add to plugins
        $this->_plugins[$name] = [
            'name' => $name,
            'class' => $ref->name,
            'type' => $ref->isSubclassOf('bolt\plugin\singleton') ? 'singleton' : 'factory',
            'ref' => $ref,
            'config' => $config,
            'instance' => false
        ];

        return $this;

    }

    public function __get($name) {
        if ($this->pluginCanCall($name)) {
            return $this->call($name);
        }
    }

    public function __call($name, $args) {
        return $this->call($name, $args);
    }

    public function call($name, $args=[]) {

        // see if we have a plugin name $name
        if (array_key_exists($name, $this->_plugins)) {
            $p = $this->_plugins[$name];

            // see if this plugin is a singleton or factory
            if ($p['type'] == 'singleton') {
                if (!$p['instance'] AND $p['ref']->hasMethod('instance')) {
                    $class = $this->_plugins[$name]['class'];
                    $p['instance'] = $class::instance($p['config']);
                }
                else if (!$p['instance']) {
                    $p['instance'] = $this->_plugins[$name]['instance'] = $p['ref']->newInstance($p['config']);
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
                return $p['ref']->newInstanceArgs([$p['config'], $args]);
            }
        }

        if (count($this->_parents) > 0) {
            foreach ($this->_parents as $parent) {
                if ($parent->pluginCanCall($name)) {
                    return $parent->call($name, $args);
                }
            }
        }

        // helpers
        if (property_exists($this, 'hasHelpers') AND $this->hasHelpers) {

            foreach ($this->_helpers as $helper) {

                if (in_array($name, $helper['methods'])) {

                    if (!$helper['instance']) {
                        $this->_helpers[$helper['name']]['instance'] = $helper['ref']->newInstance();
                    }

                    return call_user_func_array([$this->_helpers[$helper['name']]['instance'], $name], $args);
                }
            }
        }


    }

    public function pluginCanCall($name) {
        if (array_key_exists($name, $this->_plugins)) {
            return true;
        }
        foreach ($this->_parents as $parent) {
            if ($parent->pluginCanCall($name)) {
                return true;
            }
        }
        return false;
    }

}
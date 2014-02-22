<?php

namespace bolt;
use \b;

use \Exception;


/**
 * Base plugin abstract
 */
abstract class plugin implements \ArrayAccess {

    /**
     * @var array
     */
    private $_plugins = [];


    /**
     * plug a new class into this parent class
     *
     * @param string $name name of plugin
     * @param string|callback $class what to call when access
     * @param array $config arguments to pass to callback
     *
     * @return self
     */
    public function plug($name, $class=null, $config=[]) {
        if (is_array($name)) {
            foreach ($name as $plug) {
                call_user_func_array([$this, 'plug'], $plug);
            }
            return $this;
        }

        if (is_string($class) && !class_exists($class, true)) {
            throw new Exception("Unknown class $class attempted to plugin as $name");
        }

        $ref = b::getReflectionClass($class);

        $this->_plugins[$name] = [
            'ref' => $ref,
            'type' => $ref->isSubclassOf('\bolt\plugin\factory') ? 'factory' : 'singleton',
            'instance' => false,
            'initRun' => true,
            'config' => $config
        ];

        // do we have a class already
        if (!is_string($class)) {
            $this->_plugins[$name]['instance'] = $class;
            $this->_plugins[$name]['initRun'] = !$this->_plugins[$name]['ref']->hasMethod('firstRun');
        }

        // if it's a singleton, we construct right away
        else if ($this->_plugins[$name]['type'] == 'singleton') {
            $this->_constructPluginInstance($name);
            $this->_plugins[$name]['initRun'] = !$this->_plugins[$name]['ref']->hasMethod('firstRun');
        }

        return $this;

    }

    /**
     * return all plugins
     *
     * @return array list of plugins
     */
    public function getPlugins() {
        return $this->_plugins;
    }


    /**
     * does plugin exist
     *
     * @param string $name name of plugin
     *
     * @return bool value of plugin exists
     */
    public function pluginExists($name) {
        return array_key_exists($name, $this->_plugins);
    }


    /**
     * get a plugin instance
     *
     * @param string $name name of plugin
     *
     * @return mixed instance of plugin
     */
    public function plugin($name) {
        if (!$this->pluginExists($name)) {
            throw new Exception("Plugin $name does not exist", 404);
        }

        $plugin = $this->_plugins[$name];

        // factory
        if ($plugin['type'] == 'factory') {
            $class = $plugin['ref']->name;
            return $class::factory();
        }

        // no instance
        if (!$plugin['initRun']) {
            call_user_func([$plugin['instance'], 'firstRun']);
            $this->_plugins[$name]['initRun'] = true;
        }


        return $plugin['instance'];
    }


    /**
     * construct a plugin instance
     *
     * @param string $name name of plugin to construct
     *
     * @return plugin instance
     *
     */
    private function _constructPluginInstance($name) {
        $plugin = $this->_plugins[$name];
        $ref = $plugin['ref'];
        $args = [];

        // get our constructor
        $constr = $ref->getConstructor();

        // constructor and number of params
        if ($constr && $constr->getNumberOfParameters() > 0) {
            $parent = get_called_class();
            foreach ($constr->getParameters() as $p) {
                if (($c = $p->getClass()) !== null && $c->name == $parent) {
                    $args[] = $this;
                }
                else if ($p->name === 'config') {
                    $args[] = $plugin['config'];
                }
                else if ($p->isOptional()) {
                    $args[] = $p->getDefaultValue();
                }
            }
        }

        // set back globally
        return $this->_plugins[$name]['instance'] = $ref->newInstanceArgs($args);

    }


    /**
     * unplug a plugin
     *
     * @param string $name name of plugin to remove
     *
     * @return self
     */
    public function unplug($name) {
        if (!$this->pluginExists($name)) {
            throw new Exception("Unknown plugin $name");
        }

        if ($this->_plugins[$name]['instance']) {
            unset($this->_plugins[$name]['instance']);
        }

        unset($this->_plugins[$name]);

        return $this;
    }


    /**
     * set offset
     *
     * @param string $name set the name
     * @param string $class class name
     *
     * @return self
     */
    public function offsetSet($name, $class) {
        return $this->plug($name, $class);
    }


    /**
     * offset get
     *
     * @param string $name name of plugin
     *
     * @return bool
     */
    public function offsetExists($name) {
        return $this->pluginExists($name);
    }


    /**
     * unplug
     *
     * @param string $name name of plugin to unplug
     *
     * @return void
     */
    public function offsetUnset($name) {
        return $this->unplug($name);
    }


    /**
     * get a plugin
     *
     * @param string $name name of plugin
     *
     * @return mixed
     */
    public function offsetGet($name) {
        return $this->plugin($name);
    }

}
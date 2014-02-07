<?php

namespace bolt;
use \b;

class plugin implements \ArrayAccess {

    private $_plugins = [];

    /**
     * plug a new class into this parent class
     *
     * @param $name string name of plugin
     * @param $class string|callback what to call when access
     * @param $args array arguments to pass to callback
     *
     * @return self
     */
    public function plug($name, $class=null, $args=[]) {
        if (is_array($name)) {
            foreach ($name as $plug) {
                call_user_func_array([$this, 'plug'], $plug);
            }
            return $this;
        }

        if (!class_exists($class, true)) {
            throw new Exception("Unknwon class");
            return false;
        }

        $ref = b::getClassRef($class);

        $this->_plugins[$name] = [
            'ref' => $ref,
            'type' => $ref->isSubclassOf('\bolt\plugin\factory') ? 'factory' : 'singleton',
            'instance' => false,
            'initRun' => true
        ];

        // if it's a singleton, we construct right away
        if ($this->_plugins[$name]['type'] == 'singleton') {
            $this->_constructPluginInstance($name);
            $this->_plugins[$name]['initRun'] = !$this->_plugins[$name]['ref']->hasMethod('firstRun');
        }


        return $this;

    }

    public function pluginExists($name) {
        return array_key_exists($name, $this->_plugins);
    }

    /**
     * get a plugin instance
     *
     * @param $name string name of plugin
     *
     * @return mixed instance of plugin
     */
    public function plugin($name) {
        $plugin = $this->_plugins[$name];

        // factory
        if ($plugin['type'] == 'factory' AND $plugin['ref']->hasMethod('factory') ) {
            $class = $plugin['ref']->name;
            return $class::factory();
        }
        else if ($plugin['type'] == 'factory') {
            return $this->_constructPluginInstance($name);
        }

        // no instance
        if (!$plugin['initRun']) {
            call_user_func([$plugin['instance'], 'firstRun']);
            $this->_plugins[$name]['initRun'] = true;
        }


        return $plugin['instance'];
    }

    private function _constructPluginInstance($name) {
        $plugin = $this->_plugins[$name];
        $ref = $plugin['ref'];
        $args = [];

        // get our constructor
        $constr = $ref->getConstructor();

        if ($constr AND $constr->getNumberOfParameters() > 0) {
            $parent =  get_called_class();

            foreach ($constr->getParameters() as $p) {
                if (($c = $p->getClass()) !== null AND $c->name == $parent) {
                    $args[] = $this;
                }
                else if ($p->isOptional()) {
                    $args[] = $p->getDefaultValue();
                }
                else {
                    $args[] = null;
                }
            }
        }


        // set back globally
        return $this->_plugins[$name]['instance'] = $ref->newInstanceArgs($args);

    }


    public function offsetSet($name, $class) {
        return;
    }

    public function offsetExists($name) {
        return $this->pluginExists($name);
    }

    public function offsetUnset($name) {
        $this->unplug($name);
    }
    public function offsetGet($name) {
        return $this->plugin($name);
    }

}
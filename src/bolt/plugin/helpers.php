<?php

namespace bolt\plugin;

trait helpers {

    private $_helpers = [];

    public $hasHelpers = true;

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

    public function helperExists($name) {
        foreach ($this->_helpers as $helper){
            if (in_array($name, $helper['methods'])) {
                return true;
            }
        }
        return false;
    }

    public function callHelper($name, $args) {
        foreach ($this->_helpers as $i => $helper) {
            if (in_array($name, $helper['methods'])) {
                if (!$helper['instance']) {
                    $this->_helpers[$i]['instance'] = $helper['instance'] = $helper['ref']->newInstance();
                }
                return call_user_func_array([$helper['instance'], $name], $args);
            }
        }
        return false;
    }

}
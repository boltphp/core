<?php

namespace bolt\helpers;
use \b;

class classes {

    private $_ref = [];
    private $_classes = [];

    public function getReflectionClass($class) {
        $name = is_string($class) ? $this->normalizeClassName($class) : get_class($class);

        if (array_key_exists($name, $this->_ref)) {
            return $this->_ref[$name];
        }

        return $this->_ref[$name] = new \ReflectionClass($class);
    }

    public function normalizeClassName($class) {
        return ltrim($class, '\\');
    }

    public function getDeclaredClasses() {
           if (!$this->_classes) {
               $this->_classes = get_declared_classes();
           }
           return $this->_classes;
       }

    public function getClassImplements($name) {
        $implements = [];
        $name = $this->normalizeClassName($name);

        foreach ($this->getDeclaredClasses() as $class) {
            $c = $this->getReflectionClass($class);

            if (in_array($name, $c->getInterfaceNames()) ) {
                $implements[] = $c;
            }
        }

        return $implements;
    }

    public function getSubClassOf($name) {
        $classes = [];
        $name = $this->normalizeClassName($name);

        foreach ($this->getDeclaredClasses() as $class) {
            $c = $this->getReflectionClass($class);
            if ($c->name === $name OR $c->isAbstract()) {continue;}
            if ($c->isSubclassOf($name)) {
                 $classes[] = $c;
            }
        }
        return $classes;
    }

}
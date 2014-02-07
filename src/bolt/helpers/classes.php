<?php

namespace bolt\helpers;
use \b;

class classes {

    private $_ref = [];
    private $_classes = [];

    public function getClassRef($class) {
        $name = $this->normalizeClassName($class);

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
            $c = $this->getClassRef($class);

            if (in_array($name, $c->getInterfaceNames()) ) {
                $implements[] = $c;
            }
        }

        return $implements;
    }

}
<?php

namespace bolt\helpers;
use \b;


/**
 * classes helper
 */
class classes {

    /**
     * existing refrances
     *
     * @var array
     */
    private $_ref = [];


    /**
     * get the reflection class
     *
     * @param string $class
     *
     * @return ReflectionClass
     */
    public function getReflectionClass($class) {
        $name = is_string($class) ? $this->normalizeClassName($class) : get_class($class);

        if (array_key_exists($name, $this->_ref)) {
            return $this->_ref[$name];
        }

        return $this->_ref[$name] = new \ReflectionClass($class);
    }


    /**
     * normalize a class name
     *
     * @param string $class
     *
     * @return string
     */
    public function normalizeClassName($class) {
        return ltrim($class, '\\');
    }


    /**
     * get a list of declared classes
     *
     * @return array
     */
    public function getDeclaredClasses() {
        return get_declared_classes();
    }


    /**
     * get classes that implement an interface
     *
     * @param string $name class name
     *
     * @return array
     */
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


    /**
     * get classes that have class as a parent
     *
     * @param string $name
     *
     * @return array
     */
    public function getSubClassOf($name) {
        $classes = [];
        $name = $this->normalizeClassName($name);

        foreach ($this->getDeclaredClasses() as $class) {
            $c = $this->getReflectionClass($class);
            if ($c->name === $name || $c->isAbstract()) {continue;}
            if ($c->isSubclassOf($name)) {
                 $classes[] = $c;
            }
        }
        return $classes;
    }

}
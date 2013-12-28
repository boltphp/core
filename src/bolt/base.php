<?php

namespace bolt;

use \RecursiveDirectoryIterator;
use \RecursiveIteratorIterator;
use \RecursiveRegexIterator;
use \RegexIterator;

class base {

    private $_classes = false;
    private $_refClass = [];

    private $_settings = [];

    // loaded
    private $_required = [];

    public function settings($name, $value=null) {
        if ($value === null AND array_key_exists($name, $this->_settings)) {
            return $this->_settings[$name];
        }
        return $this->_settings[$name] = new \bolt\bucket\a($value);
    }

    public function getRegexFiles($path, $regex="^.+\.php$") {
        $files = [];
        $regex = new RegexIterator(
                new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path)),
                '#'.$regex.'#i',
                RecursiveRegexIterator::GET_MATCH
        );
        foreach (iterator_to_array($regex) as $path) {
            $files[] = realpath($path[0]);
        }
        return $files;
    }

    public function requireFromPath($path, $regex="^.+\.php$") {
        $paths = $this->getRegexFiles($path, $regex);
        array_walk($paths, function($path) {
            if (!in_array($path, $this->_required)) {
                $this->_required[] = $path;
                require_once($path);
            }
        });
        return $this;
    }

    public function getDefinedClasses() {
        if (!$this->_classes) {
            $this->_classes = get_declared_classes();
        }
        return $this->_classes;
    }

    public function getReflectionClass($class) {
        $normal = $this->normalizeClassName($class);
        if (!array_key_exists($normal, $this->_refClass)) {
            $this->_refClass[$normal] = is_a($class, 'ReflectionClass') ? $class : new \ReflectionClass($class);
        }
        return $this->_refClass[$normal];
    }

    public function normalizeClassName($class) {
        return ltrim($class, '\\');
    }

    public function getClassImplements($name) {
        $implements = [];
        $name = $this->normalizeClassName($name);

        foreach ($this->getDefinedClasses() as $class) {
            $c = $this->getReflectionClass($class);

            if (in_array($name, $c->getInterfaceNames()) ) {
                $implements[] = $c;
            }
        }

        return $implements;
    }

}
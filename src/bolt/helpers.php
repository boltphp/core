<?php

namespace bolt;

class helpers {

    private $_classes = false;
    private $_refClass = [];


    // loaded
    private $_required = [];

    public static function path() {
        $sep = "/";
        return $sep.implode($sep, array_map(function($val) use ($sep){ return trim($val, $sep); }, func_get_args()));
    }

    public static function param($key, $default=false, $object=[]) {

        return array_key_exists($key, $object) ? $object[$key] : $default;
    }

    public function isInterfaceOf($obj, $class) {
        if (!is_object($obj)){ return false; }
        return in_array(b::normalizeClassName($class), class_implements($obj));
    }

    public function getRegexFiles($path, $regex="^.+\.php$") {
        $files = [];

        $findFiles = function($path, &$files, $findFiles) use ($regex) {
            $dirs = [];

            foreach (new \DirectoryIterator($path) as $file) {
                if ($file->isDot()) {continue;}

                if ($file->isFile() AND preg_match('#'.$regex.'#i', $file->getPathname())) {
                    $files[] = $file->getRealPath();
                }
                else if ($file->isDir()) {
                    $dirs[] = $file->getPathname();
                }
            }
            foreach ($dirs as $dir) {
                $findFiles($dir, $files, $findFiles);
            }

        };

        $findFiles(realpath($path), $files, $findFiles);

        return $files;
    }

    public function requireFromPath($path, $regex="^.+\.php$") {
        $paths = $this->getRegexFiles($path, $regex);


        foreach ($paths as $path) {
            if (!in_array($path, $this->_required)) {
                $this->_required[] = $path;
                require_once($path);
            }
        }
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

    public function getClassExtends($name) {
        $extends = [];
        $name = $this->normalizeClassName($name);

        foreach ($this->getDefinedClasses() as $class) {
            $c = $this->getReflectionClass($class);
            if ($c->getParentClass() AND $c->getParentClass()->name == $name) {
                $extends[] = $c;
            }
        }

        return $extends;
    }

}
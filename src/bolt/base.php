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

    private $_classes = false;
    private $_refClass = [];

    private $_settings = false;

    // loaded
    private $_required = [];

    public function __construct() {
        $this->loader = new ClassLoader();
        $this->loader->setUseIncludePath(true);
    }

    public function load($prefix, $path) {
        $this->loader->addPrefix($prefix, $path);
        $this->loader->register();
        return $this;
    }

    public function settings($name, $value=null) {
        if (!$this->_settings) { $this->_settings = new bucket\a(); }
        if ($value === null) {
            return $this->_settings->get($name);
        }
        else {
            $this->_settings->set($name, $value);
        }
        return $this;
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
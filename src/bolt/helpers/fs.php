<?php

namespace bolt\helpers;
use \b;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;


class fs {

    private $_required = [];

    private $_fs;

    public static function path() {
        $sep = DIRECTORY_SEPARATOR;
        return $sep.ltrim(implode($sep, array_map(function($val) use ($sep){ return trim($val, $sep); }, func_get_args())), $sep);
    }

    public function getRegexFiles($path, $regex="/^.+\.php$/") {
        $files = [];

        if (!is_dir($path)) {return [];}

        $finder = new Finder();
        $finder
            ->ignoreVCS(true)
            ->followLinks()
            ->files()
            ->name($regex)
            ->in($path)
        ;

        foreach($finder as $file) {
            $files[] = $file->getRealPath();
        }

        return $files;
    }

    public function requireFromPath($path, $regex="/^.+\.php$/") {
        $paths = $this->getRegexFiles($path, $regex);

        foreach ($paths as $path) {                        
            require_once($path);            
        }
        return $this;
    }

    public function fs() {
        $args = func_get_args();
        $type = array_shift($args);

        if (!$this->_fs) {
            $this->_fs = new Filesystem();
        }

        if (method_exists($this->_fs, $type)) {
            return call_user_func_array([$this->_fs, $type], $args);
        }

        $class = '\bolt\helpers\fs\\'.$type;

        if (!class_exists($class, true)) {
            throw new \Exception("Unknown FS class {$type}");
        }

        $ref = b::getReflectionClass($class);

        return $ref->newInstanceArgs($args);

    }

}
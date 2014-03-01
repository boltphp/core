<?php

namespace bolt\helpers;
use \b;

use Symfony\Component\Filesystem\Filesystem;

class fs {

    private $_required = [];

    private $_fs;

    public static function path() {
        $sep = DIRECTORY_SEPARATOR;
        return $sep.ltrim(implode($sep, array_map(function($val) use ($sep){ return trim($val, $sep); }, func_get_args())), $sep);
    }

    public function getRegexFiles($path, $regex="^.+\.php$") {
        $files = [];

        if (!is_dir($path)) {return [];}

        $findFiles = function($path, &$files, $findFiles) use ($regex) {
            $dirs = [];

            foreach (new \DirectoryIterator($path) as $file) {
                if ($file->isDot()) {continue;}

                if ($file->isFile() && preg_match('#'.$regex.'#i', $file->getPathname())) {
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
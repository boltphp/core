<?php

namespace bolt\helpers;

class fs {

    private $_required = [];

    public static function path() {
        $sep = DIRECTORY_SEPARATOR;
        return $sep.implode($sep, array_map(function($val) use ($sep){ return trim($val, $sep); }, func_get_args()));
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

}
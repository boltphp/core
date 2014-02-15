<?php

namespace bolt\helpers\fs;
use \b;

class rdir {

    private $_path;
    private $_regex;

    public function __construct($path, $regex = null) {

        // RecursiveDirectoryIterator

        $this->_path = $path;
        $this->_regex = $regex;

    }

    public function asArray() {
        if ($this->_regex) {
            return b::getRegexFiles($this->_path, $this->_regex);
        }
    }

}
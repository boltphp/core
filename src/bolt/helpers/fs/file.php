<?php

namespace bolt\helpers\fs;
use \b;

use \SplFileObject;

class file extends SplFileObject {

    private $_file;

    public function __construct($file) {
        $this->_file = $file;
    }

}
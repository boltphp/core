<?php

namespace bolt\helpers\fs;
use \b;

use \GlobIterator,
    \FilesystemIterator;

class glob extends GlobIterator {

    public function __construct($path, $flags = FilesystemIterator::KEY_AS_PATHNAME) {
        parent::__construct($path, $flags);
    }


    public function asArray() {
        return array_keys(iterator_to_array($this));
    }

}
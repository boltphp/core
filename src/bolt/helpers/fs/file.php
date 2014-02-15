<?php

namespace bolt\helpers\fs;
use \b;

use \SplFileObject;

class file extends SplFileObject {

    public function __toString() {
        return $this->getPathName();
    }

}
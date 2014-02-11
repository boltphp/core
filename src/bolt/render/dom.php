<?php

namespace bolt\render;
use \b;

class dom extends base {


    public function compile() {


    }

    public function render($str, $vars) {
        return new \bolt\dom($str);
    }


}
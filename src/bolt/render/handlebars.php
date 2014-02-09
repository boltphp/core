<?php

namespace bolt\render;
use \b;

use Handlebars\Handlebars as HBR;

class handlebars extends base {

    private static $_instance;

    public function __construct() {
        if (!self::$_instance) {
            self::$_instance = new HBR([
                    'delimiters' => "<% %>",
                ]);
        }
    }

    public function compile() {


    }

    public function render($str, $vars) {
        return self::$_instance->render($str, $vars);
    }


}
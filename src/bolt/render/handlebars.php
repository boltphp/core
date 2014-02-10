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
            self::$_instance->addHelper('=', function($template, $context, $args, $source) {
                $ctx = $context->get('context');
                $func = function($args) {
                    return eval('return '.trim($args, '; ').';');
                };
                return call_user_func($func->bindto($ctx, $ctx), $args);
            });
        }
    }

    public function compile() {


    }

    public function render($str, $vars) {
        return self::$_instance->render($str, $vars);
    }


}
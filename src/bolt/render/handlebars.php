<?php

namespace bolt\render;
use \b;

use Handlebars\Handlebars as HBR;

/**
 * handlare renderer
 */
class handlebars extends base {

    /**
     * @var Handlebars\Handlebars
     */
    private static $_instance;


    /**
     * Constructor
     */
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


    /**
     * compile to freezable class
     */
    public function compile() {}


    /**
     * render a handlebar template
     *
     * @param string $str handlebar template
     * @param array $vars
     *
     * @return string
     */
    public function render($str, $vars = []) {
        return self::$_instance->render($str, $vars);
    }


}
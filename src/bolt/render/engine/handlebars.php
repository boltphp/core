<?php

namespace bolt\render\engine;
use \b;

// closure
use \Closure;

// use handlebars
use Handlebars\Handlebars as HBR;

class handlebars extends base {

    const EXT = 'hbr';

    private $_instance = false;

    public function __construct() {
        $this->_instance = new HBR([
                'delimiters' => "<% %>",
            ]);

        // add our helpers
        $this->_instance->addHelper('bolt', function($template, $context, $args, $source){
            return eval('return bolt::'.trim($args, '; ').';');
        });

        $this->_instance->addHelper('=', function($template, $context, $args, $source){
            $ctx = $context->get('context');
            $func = function($args) {
                return eval('return '.trim($args, '; ').';');
            };
            return call_user_func($func->bindto($ctx, $ctx), $args);
        });

    }

    public function compile() {

    }

    public function render($str, $config) {
        $config['vars']['context'] = $config['context'];

        return $this->_instance->render($str, $config['vars']);
    }

}
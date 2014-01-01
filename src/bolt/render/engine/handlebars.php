<?php

namespace bolt\render\engine;
use \b;

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

    }

    public function compile() {

    }

    public function render($str, $config) {

        return $this->_instance->render($str, $config['vars']);
    }

}
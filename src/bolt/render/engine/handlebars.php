<?php

namespace bolt\render\engine;
use \b;

// use handlebars
use Handlebars\Handlebars;


class handlebars extends \bolt\render\engine {

    const EXT = 'hbr';

    private $_instance = false;

    public function __construct() {
        $this->_instance = new Handlebars(

            );
    }

    public function compile() {

    }

    public function render() {

    }

}
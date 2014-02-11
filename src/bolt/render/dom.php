<?php

namespace bolt\render;
use \b;


/**
 * render a dom string
 */
class dom extends base {


    /**
     * compile the dom doc to serializeable object
     */
    public function compile() {


    }


    /**
     * render an html string into a dom doc
     *
     * @param string $var HTML
     * @param array $vars
     *
     * @return bolt\dom
     */
    public function render($str, $vars = []) {
        return new \bolt\dom($str);
    }


}
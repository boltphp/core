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
    public function compile($str) {
        return $str;
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
        return stripos($str, '<html') !== false ? new \bolt\dom($str) : new \bolt\dom\fragment($str);
    }

    public function renderCompiled($compiled, $vars = []) {
        return $this->render($compiled, $vars);
    }

}
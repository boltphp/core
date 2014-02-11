<?php

namespace bolt\render;


/**
 * base render engine class
 */
abstract class base {

    /**
     * compile abstract
     *
     * @return string
     */
    abstract public function compile();


    /**
     * render abstract
     *
     * @param string $str string to render
     * @param array $vars variables to use in rendering
     *
     * @return mixed
     */
    abstract public function render($str, $vars = []);

}
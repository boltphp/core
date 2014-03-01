<?php

namespace bolt\render;


/**
 * base render engine class
 */
abstract class base {

    protected $manager = false;

    public static function canCompile() {
        return false;
    }

    final public function __construct(\bolt\render $manager) {
        $this->manager = $manager;
        $this->init();
    }

    protected function init() {

    }


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
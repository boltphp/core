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

    final public function renderFile($file, $vars = []) {
        return $this->render(file_get_contents($file), $vars);
    }

    public function renderCompiled($compiled, $vars = []) {
        throw new \Exception("This renderer has not defined a 'renderCompiled' function.");
    }

}
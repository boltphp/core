<?php

namespace bolt\render;


/**
 * base render engine class
 */
abstract class base implements renderInterface {

    /**
     * render manager
     * 
     * @var \bolt\render
     */
    protected $manager;

    /**
     * can this renderer compile a view
     * 
     * @return bool
     */
    public static function canCompile() {
        return false;
    }

    /**
     * Constructor (can not be overloaded)
     * 
     * @param bolt\render $manager
     * 
     */
    final public function __construct(\bolt\render $manager) {
        $this->manager = $manager;
        $this->init();
    }

    /**
     * inialize the render
     * @return void
     */
    protected function init() {}


    /**
     * render abstract
     *
     * @param string $str string to render
     * @param array $vars variables to use in rendering
     *
     * @return mixed
     */
    abstract public function render($str, array $vars = []);


    /**
     * render a file
     * 
     * @param  string $file
     * @param  array $vars
     * 
     * @return string
     */
    final public function renderFile($file, array $vars = []) {
        return $this->render(file_get_contents($file), $vars);
    }


    /**
     * render a compiled vide
     * 
     * @param  mixed $compiled
     * @param  array $vars
     * 
     * @return string
     */
    public function renderCompiled($compiled, array $vars = []) {
        throw new \Exception("This renderer has not defined a 'renderCompiled' function.");
    }

}
<?php

namespace bolt;

class render {

    /**
     * @var array
     */
    private $_engines = [];

    /**
     * app instance
     *
     * @var \bolt\application
     */
    private $_app;


    /**
     * Constructor
     * 
     * @param application $app
     */
    public function __construct(application $app) {
        $this->_app = $app;
    }


    /**
     * register a new engine
     *
     * @param string $ext
     * @param string $class
     *
     * @return self
     */
    public function engine($ext, $class) {
        if (!class_exists($class, true)) {
            throw new \Exception("Render class '$class' does not exist");
        }
        if (!in_array('bolt\render\renderInterface', class_implements($class, true))) {
            throw new \Exception("Render class '$class' does not implement 'bolt\\render\\renderInterface");
        }
        $this->_engines[strtolower($ext)] = [
            'class' => $class,
            'instance' => false,
            'canCompile' => $class::canCompile()
        ];
        return $this;
    }


    /**
     * is there an engine available for the
     * provided file extension
     * 
     * @param  string  $ext
     * @param  boolean  $mustCompile
     * 
     * @return boolean
     */
    public function hasEngine($ext, $mustCompile = null) {
        if ($mustCompile === true) {
            return array_key_exists(strtolower($ext), $this->_engines) && $this->_engines[$ext]['canCompile'];
        }
        return array_key_exists(strtolower($ext), $this->_engines);
    }


    /**
     * return all registered engines
     * 
     * @return array
     */
    public function getEngines() {
        return $this->_engines;
    }


    /**
     * get an engine for the provided file extension
     * 
     * @param  string $ext
     * 
     * @return \bolt\render\face
     */
    public function getEngine($ext) {
        if (!$this->_engines[$ext]['instance']) {
            $this->_engines[$ext]['instance'] = new $this->_engines[$ext]['class']($this);
        }
        return $this->_engines[$ext]['instance'];
    }


    /**
     * render a string using the provided renderer
     * 
     * @param  string $ext
     * @param  string $str
     * @param  array $vars
     * 
     * @return mixed
     */
    public function string($ext, $str, array $vars = []) {
        if (!$this->hasEngine($ext)) {
            throw new \Exception("Unable to locate render engine for '$ext'.");
        }
        return $this->getEngine($ext)->render($str, $vars);
    }


    /**
     * render a file
     * 
     * @param  string $file
     * @param  array $vars
     * 
     * @return mixed
     */
    public function file($file, array $vars = []) {
        $ext = strtolower(pathinfo($file)['extension']);
        if (!$this->hasEngine($ext)) {
            throw new \Exception("Unable to locate render engine for '$ext'.");
        }
        if (!file_exists($file)) {
            throw new \Exception("File '$file' does not exists");
        }
        return $this->string($ext, file_get_contents($file), $vars);
    }

}
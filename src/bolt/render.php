<?php

namespace bolt;

class render {

    /**
     * @var array
     */
    private $_engines = [];


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
        $this->_engines[strtolower($ext)] = [
            'class' => $class,
            'instance' => false,
            'canCompile' => $class::canCompile()
        ];
        return $this;
    }

    public function hasEngine($ext, $mustCompile = null) {
        if ($mustCompile === true) {
            return array_key_exists(strtolower($ext), $this->_engines) && $this->_engines[$ext]['canCompile'];
        }
        return array_key_exists(strtolower($ext), $this->_engines);
    }

    public function getEngines() {
        return $this->_engines;
    }

    public function getEngine($ext) {
        if (!$this->_engines[$ext]['instance']) {
            $this->_engines[$ext]['instance'] = new $this->_engines[$ext]['class']($this);
        }
        return $this->_engines[$ext]['instance'];
    }

    public function string($ext, $str, $vars = []) {
        if (!$this->hasEngine($ext)) {
            throw new \Exception("Unable to locate render engine for '$ext'.");
        }
        return $this->getEngine($ext)->render($str, $vars);
    }

    public function file($file, $vars = []) {
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
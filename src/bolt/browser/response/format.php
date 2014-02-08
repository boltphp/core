<?php

namespace bolt\browser\response;

use \Symfony\Component\HttpFoundation\ResponseHeaderBag;

class format implements format\face {

    public $headers;

    private $_parent;

    private $_content;

    public function __construct(\bolt\browser\response $parent) {
        $this->_parent = $parent;
        $this->headers = new ResponseHeaderBag();
    }

    public function setHeader($name, $value, $replace = true) {
        $this->headers->set($name, $value, $replace);
        return $this;
    }

    public function useLayout() {
        return false;
    }

    public function setContent($content) {
        $this->_content = $content;
        return $this;
    }

    public function getContent() {
        return $this->_content;
    }

    public function __invoke() {

        // if content is a closure
        while (is_callable($this->_content)) {
            $this->_content = call_user_func($this->_content);
        }

        // set any headers we have
        foreach ($this->headers->all() as $name => $value) {
            $this->_parent->headers->set($name, $value);
        }

        // re
        $this->_parent->headers->set('Content-Type', $this->contentType);

        // format
        if (method_exists($this, 'format')) {
            $this->_content = $this->format($this->_content);
        }


        return $this->_content;


    }

}
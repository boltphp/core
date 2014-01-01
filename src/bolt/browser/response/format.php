<?php

namespace bolt\browser\response;

class format implements format\face {

    private $_parent;
    private $_content;

    public function __construct(\bolt\browser\response $parent) {
        $this->_parent = $parent;
    }

    public function __get($name) {
        return $this->_parent->{$name};
    }

    public function __set($name, $value) {
        $this->_parent->{$name} = $value;
        return $this;
    }

    public function __call($name, $args) {
        if (method_exists($this->_parent, $name) AND substr(strtolower($name),0,3) === 'set') { var_dump('x'); die;
            call_user_func_array([$this->_parent, $name], $args);
            return $this;
        }
        else if (method_exists($this->_parent, $name)) {
            return call_user_func_array([$this->_parent, $name], $args);
        }
        return false;
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

    public function send() {


        // set our proper content type
        $this->headers->set('Content-Type', $this->contentType);

        // format
        if (method_exists($this, 'format')) {
            $this->_content = $this->format( $this->_content);
        }

        $this->_parent->setContent($this->_content);

        // send
        $this->_parent->send();

    }

}
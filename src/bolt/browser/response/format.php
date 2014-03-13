<?php

namespace bolt\browser\response;

use \Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Abstract format class
 */
abstract class format implements format\face {

    /**
     * @var \Symfony\Component\HttpFoundation\ResponseHeaderBag
     */
    public $headers;

    /**
     * @var bolt\browser\response
     */
    private $_parent;

    /**
     * @var mixed
     */
    private $_content;

    /**
     * @var string
     */
    protected $contentType = "text/plain";


    /**
     * Construct
     *
     * @param bolt\browser\response $parent
     *
     */
    public function __construct(\bolt\browser\response $parent) {
        $this->_parent = $parent;
        $this->headers = new ResponseHeaderBag();
    }

    public function __call($name, $args) {
        return call_user_func_array([$this->_parent, $name], $args);
    }

    public function __get($name) {
        return $this->_parent->{$name};
    }

    /**
     * set a header
     *
     * @param string $name header name
     * @param string|array $value value of header
     * @param bool $replace
     *
     * @return self
     */
    public function setHeader($name, $value, $replace = true) {
        $this->headers->set($name, $value, $replace);
        return $this;
    }


    /**
     * set the format content
     *
     * @param mixed $content
     *
     * @return self
     */
    public function setContent($content) {
        $this->_content = $content;
        return $this;
    }


    /**
     * get the content
     *
     * @return mixed
     */
    public function getContent() {
        return $this->_content;
    }


    /**
     * get the content type
     *
     * @return string
     */
    public function getContentType() {
        return $this->contentType;
    }

    /**
     * invoke the format and return content
     *
     * @return mixed
     */
    public function __invoke() {

        // if content is a closure
        while (is_callable($this->_content)) {
            $this->_content = call_user_func(\Closure::bind($this->_content, $this));
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
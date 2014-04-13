<?php

namespace bolt\http\response;

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
     * @var bolt\http\response
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
     * @param bolt\http\response $parent
     *
     */
    public function __construct(\bolt\http\response $parent) {
        $this->_parent = $parent;
        $this->headers = new ResponseHeaderBag();

        $this->headers->set('Content-Type', $this->contentType);

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
            if (is_a($this->_content, '\Closure')) {
                $this->_content = call_user_func(\Closure::bind($this->_content, $this));
            }
            else {
                $this->_content = call_user_func($this->_content);
            }
        }

        // format
        if (method_exists($this, 'format')) {
            $this->_content = $this->format($this->_content);
        }

        return $this->_content;


    }

}
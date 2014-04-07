<?php

namespace bolt\http;
use \b;

/// require symfony response
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * response object
 */
class response extends SymfonyResponse {

    /**
     * unique id for this response
     *
     * @var string
     */
    private $_guid;

    /**
     * is response ready to send
     *
     * @var boolean
     */
    private $_readyToSend = false;

    /**
     * exception attached to response
     *
     * @var Exception
     */
    private $_exception = null;


    /**
     * formats of this response
     *
     * @var array
     */
    private $_formats = [];

    /**
     * Constructor
     */
    public function __construct() {
        $this->_guid = b::guid('req');
        call_user_func_array([get_parent_class(), '__construct'], func_get_args());
    }


    /**
     * add a response format
     *
     * @param string|array $format format name or array of formats
     * @param mixed $content
     *
     * @return bolt\http\response\format
     */
    public function format($format, $content=false) {
        if (is_array($format)) {
            array_walk($format, function($content, $format){
                $this->format($format, $content);
            });
            return $this;
        }

        $class = $format;

        if (!class_exists($class, true)) {
            $class = 'bolt\http\response\format\\'.$format;
        }

        if (!class_exists($class, true)) {
            throw new \Exception("Unknown format class $class");
        }

        $o = new $class($this);


        if (!is_subclass_of($o, 'bolt\http\response\format\face')) {
            throw new \Exception('Format class does not implement bolt\http\response\format\face');
        }

        $this->_formats[$format] = $o;
        $this->_formats[$format]->setContent($content);
        return $this->_formats[$format];
    }


    /**
     * does this response have a foramt
     *
     * @param string $format
     *
     * @return boolean
     */
    public function hasFormat($format) {
        return array_key_exists($format, $this->_formats);
    }



    /**
     * set a response exception
     *
     * @param Exception $e [description]
     *
     * @return self
     */
    public function setException(\Exception $e) {
        $this->_exception = $e;
        if (array_key_exists($e->getCode(), SymfonyResponse::$statusTexts)) {
            $this->setStatusCode($e->getCode());
        }
        $this->readyToSend();
        return $this;
    }


    /**
     * does the response have an exception
     *
     * @return boolean
     */
    public function hasException() {
        return $this->_exception !== null;
    }


    /**
     * get eh response exception
     *
     * @return mixed Exception or null
     */
    public function getException() {
        return $this->_exception;
    }


    /**
     * get or set the response $_readyToSend
     *
     * @param  boolean $ready
     *
     * @return boolean
     */
    public function isReadyToSend($ready = null) {
        return $ready === null ? $this->_readyToSend : $this->_readyToSend = $ready;
    }


    /**
     * set response as ready to send
     *
     * @return self
     */
    public function readyToSend() {
        $this->_readyToSend = true;
        return $this;
    }

}
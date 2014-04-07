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
     * Constructor
     */
    public function __construct() {
        $this->_guid = b::guid('req');
        call_user_func_array([get_parent_class(), '__construct'], func_get_args());
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
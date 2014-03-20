<?php

namespace bolt\browser;
use \b;

/// require symfony response
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * response object
 */
class response extends SymfonyResponse {

    private $_guid;

    private $_readyToSend = false;

    private $_exception = null;

    /**
     * Constructor
     */
    public function __construct() {
        $this->_guid = b::guid('req');
        call_user_func_array([get_parent_class(), '__construct'], func_get_args());
    }

    public function setException(\Exception $e) {
        $this->_exception = $e;
        if (array_key_exists($e->getCode(), SymfonyResponse::$statusTexts)) {
            $this->setStatusCode($e->getCode());
        }
        $this->readyToSend();
        return $this;
    }

    public function hasException() {
        return $this->_exception !== null;
    }

    public function getException() {
        return $this->_exception;
    }

    public function isReadyToSend($ready = null) {
        return $ready === null ? $this->_readyToSend : $this->_readyToSend = $ready;
    }

    public function readyToSend() {
        $this->_readyToSend = true;
        return $this;
    }

}
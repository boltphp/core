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

    /**
     * Constructor
     */
    public function __construct() {
        $this->_guid = b::guid('req');
        call_user_func_array([get_parent_class(), '__construct'], func_get_args());
    }

    public function isReadyToSend($ready = null) {
        return $ready === null ? $this->_readyToSend : $this->_readyToSend = $ready;
    }

    public function readyToSend() {
        $this->_readyToSend = true;
        return $this;
    }

}
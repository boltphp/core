<?php

namespace bolt\helpers;
use \b;

trait loggable {

    protected $logPrefix = null;

    protected $_logInstance;

    public function setLogInstance(\bolt\log $handler) {
        $this->_logInstance = $handler;
        return $this;
    }

    public function getLogInstance() {
        if ($this->_logInstance) {
            return $this->_logInstance;
        }
        if (method_exists($this, 'getApp')) {
            return $this->getApp()['log'];
        }
        return null;
    }

    public function log() {        
        $handler = $this->getLogInstance();
        if (!$handler) {
            return;
        }
        $args = func_get_args();
        $type = array_shift($args);
        if ($this->logPrefix) {
            array_unshift($args, $this->logPrefix);
        }        
        call_user_func_array([$handler, $type], $args);
        return $this;
    }

}
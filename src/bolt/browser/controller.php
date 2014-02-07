<?php

namespace bolt\browser;
use \b;

class controller {

    protected $layout = null;

    private $_parameters = [];

    private $_browser;

    public function init() {

    }

    public function __set($name, $value) {
        $this->_parameters[$name] = $value;
    }
    protected function getArgsFromMethodRef($ref, $params) {

        // we need to get all args
        // from the function and see what we can prefill
        $args = [];

        foreach ($ref->getParameters() as $param) {
            if ($param->getClass() AND $param->getClass()->name === 'bolt\browser\request') {
                $args[] = $this->request;
            }
            else if ($param->getClass() AND $param->getClass()->name === 'bolt\browser\response') {
                $args[] = $this->response;
            }
            else if (array_key_exists($param->getName(), $params)) {
                $args[] = $params[$param->getName()];
            }
            else if ($param->isOptional()) {
                $args[] = $param->getDefaultValue();
            }
            else if ($param->isOptional()) {
                $args[] = null;
            }
        }

        return $args;
    }

}
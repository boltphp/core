<?php

namespace bolt\browser;
use \b;

class controller {

    private $_parameters;

    private $_browser;

    public function init() {

    }

    public function __set($name, $value) {
        $this->_parameters[$name] = $value;
    }

    public function setBrowser(\bolt\browser $browser) {
        $this->_browser = $browser;
    }

    public function view($filename, $vars=[]) {


        // if we have a browser
        // lets
        if ($this->_browser) {

            $paths = $this->_browser->getPath('views');

            var_dump($paths);

        }

        return new view([
                'file' => $filename,
                'vars' => $vars,
                'parent' => $this
            ]);

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
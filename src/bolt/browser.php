<?php

namespace bolt;
use \b;



class browser {

    private $_request;
    private $_response;

    private $_middleware;

    private $_app;

    public function __construct(\bolt\application $app) {

        $this->_app = $app;

        $app->on('run', [$this, 'run']);

        // new request and response
        $this->_request = browser\request::createFromGlobals();
        $this->_response = new browser\response;

    }

    public function getRequest() {
        return $this->_request;
    }

    public function setRequest(\bolt\browser\request $req) {
        $this->_request = $req;
        return $this;
    }

    public function getResponse() {
        return $this->_repsonse;
    }

    public function setResponse(\bolt\browser\response $resp) {
        $this->_repsonse = $resp;
        return $this;
    }


    public function run() {

        // if we have a router
        // we need to match some routers
        if (isset($this->_app['router'])) {

            // run the request router againts this request
            $match = $this->_app['router']->match($this->_request);

            var_dump($match); die;

        }

    }

}
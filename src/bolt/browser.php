<?php

namespace bolt;
use \b;



class browser {
    use events;

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
        return $this->_response;
    }

    public function setResponse(\bolt\browser\response $resp) {
        $this->_response = $resp;
        return $this;
    }

    public function bind($name, $class = null, $args = []) {
        if (is_array($name)) {
            foreach ($name as $item) {
                call_user_func_array([$this, 'bind'], $item);
            }
            return $this;
        }

        // annon function
        if (is_callable($name)) {
            $class = 'bolt\browser\middleware\closure';
            $inst = new $class($config);
            $inst->setClosure($name);
            $name = 'closure'.microtime();
        }

        // get the reflection class
        $ref = b::getReflectionClass($class);

        $this->_middleware[] = [
            'name' => $name,
            'ref' => $ref,
            'instance' => $inst,
            'class' => $class,
            'config' => $config
        ];

        return $this;
    }


    public function run() {

        // if we have a router
        // we need to match some routers
        if (isset($this->_app['router'])) {

            // run the request router againts this request
            $params = $this->_app['router']->match($this->_request);

            // bind our params to request::attributes
            $this->_request->attributes->replace($params);

            // create our controller
            $controller = new $params['_controller']($this->_app, $this);

        }

    }

}
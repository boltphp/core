<?php

namespace bolt;
use \b;

use \Exception;

/**
 * Base browser (http) handler
 */
class browser {
    use events;

    /**
     * @var bolt\browser\request
     */
    private $_request;

    /**
     * @var bolt\browser\response
     */
    private $_response;

    /**
     * @var bolt\browser\middleware[]
     */
    private $_middleware = [];

    /**
     * @var bolt\application
     */
    private $_app;


    /**
     * Constructor.
     *
     * @param bolt\application $app Application instance
     * @param bolt\browser\request $req request object
     * @param  bolt\browser\response $resp response object
     *
     */
    public function __construct(application $app, browser\request $req = null,  browser\response $resp = null) {
        $this->_app = $app;

        // we need to
        $app->on('run', [$this, 'run']);

        // new request and response
        $this->_request = $req ?: browser\request::createFromGlobals();
        $this->_response = $resp ?: new browser\response;

    }


    /**
     * returns a private variable
     *
     * @param string $name name of variable
     *
     * @return mixed
     */
    public function __get($name) {
        switch ($name) {
            case 'app':
                return $this->_app;
            case 'request':
                return $this->_request;
            case 'response':
                return $this->_response;
        };
        return false;
    }


    /**
     * call a method or passthrough to other plugin
     *
     * @param string $name name of method
     * @param array $args array of call arguments
     *
     * @return mixed
     */
    public function __call($name, $args) {

        // shortcut to router methods
        if (in_array($name, ['get','post','put','delete','head'])) {
            if (!isset($this->_app['router'])) { throw new Exception("No router plugin defined"); return; }
            return call_user_func_array([$this->_app['router'], $name], $args);
        }

    }

    /**
     * return the app instance
     *
     * @return bolt\application
     */
    public function getApp() {
        return $this->_app;
    }


    /**
     * return the request
     *
     * @return bolt\browser\request
     */
    public function getRequest() {
        return $this->_request;
    }


    /**
     * set the response
     *
     * @param bolt\browser\reqest $req new request object
     *
     * @return self
     */
    public function setRequest(\bolt\browser\request $req) {
        $this->_request = $req;
        return $this;
    }


    /**
     * get the response object
     *
     * @return bolt\browser\response
     */
    public function getResponse() {
        return $this->_response;
    }

    /**
     * set the response object
     *
     * @param bolt\browser\response $resp new response object
     *
     * @return self
     */
    public function setResponse(\bolt\browser\response $resp) {
        $this->_response = $resp;
        return $this;
    }


    /**
     * bind middleware to this request and response
     *
     * @param string|callback $name name of middleware or callback function
     * @param string|boject $class name or instance of middle object
     * @param array $config configuration passed to instance constructor
     *
     * @return self
     */
    public function bind($name, $class = null, $config = []) {
        if (is_array($name)) {
            foreach ($name as $item) {
                if (!is_array($item)) {$item = [$item]; }
                call_user_func_array([$this, 'bind'], $item);
            }
            return $this;
        }

        $inst = false;

        // annon function
        if (is_object($class)) {
            $inst = $class;
        }
        if (is_callable($name)) {
            $class = 'bolt\browser\middleware\closure';
            $inst = new $class($this, $config);
            $inst->setClosure($name);
            $name = 'closure'.microtime();
        }

        // get the reflection class
        $ref = b::getReflectionClass($class);

        $this->_middleware[$name] = [
            'name' => $name,
            'ref' => $ref,
            'instance' => $inst,
            'class' => $ref->name,
            'config' => $config,
            'methods' => array_map(function($m) {return $m->name;}, $ref->getMethods())
        ];

        return $this;
    }


    /**
     * run the browser request and send a response to the browser
     *
     * @return void
     */
    public function run() {

        // run before we have run any router
        $this->runMiddleware('before');

        // if we have a router
        // we need to match some routers
        if (isset($this->_app['router'])) {

            // run the request router againts this request
            $params = $this->_app['router']->match($this->_request);

            // bind our params to request::attributes
            $this->_request->attributes->replace($params);

            // create our controller
            $controller = new $params['_controller']($this);

            // run before we have run any router
            $this->runMiddleware('handle', ['controller' => $controller]);

            // run the controller
            $this->_response = $controller->run($params);

        }
        else {
            $this->runMiddleware('handle');
        }


        // run before we have run any router
        $this->runMiddleware('after');

        // prepare our response
        $this->_response->prepare($this->_request);

        // send a response
        $this->_response->send();

    }


    /**
     * run all middleware for a method
     *
     * @param string $method name of method to run
     * @param array $params array of params to pass to method
     *
     * @return self
     */
    public function runMiddleware($method, $params = []) {
        foreach ($this->_middleware as $ware) {
            if (!in_array($method, $ware['methods'])) {continue;}
            $this->runMiddlewareByName($ware['name'], $method, $params);
        }
        return $this;
    }


    /**
     * run a middleware function by name
     *
     * @param string $name name of middleware to run
     * @param string $method name of middleware method to run
     * @param array $params aray of params to pass to middleware
     *
     * @return mixed results of middleware execution
     */
    public function runMiddlewareByName($name, $method, $params = []) {
        if (!array_key_exists($name, $this->_middleware)) {
            throw new Exception("Unknown middleware '$name'");
            return false;
        }
        $ware = $this->_middleware[$name];
        if (!$ware['instance']) {
            $this->_middleware[$ware['name']] = $ware['instance'] = $ware['ref']->newInstance($this, $ware['config']);
        }
        return $ware['instance']->execute($method, $params);
    }

}
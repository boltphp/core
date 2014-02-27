<?php

namespace bolt;
use \b;

use \Exception;

/**
 * Base browser (http) handler
 */
class browser extends plugin {
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
     * start a new browser instance
     */
    public static function start($config = []) {
        $app = b::init($config);
        $app->plug('browser', 'bolt\browser');
        return $app['browser'];
    }

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
        $app->on('run:browser', [$this, 'execute']);

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
            if (!isset($this['router'])) { throw new Exception("No router plugin defined"); }
            return call_user_func_array([$this['router'], $name], $args);
        }

        return null;

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
     * get a path relative to root
     *
     * @see bolt\application::path
     *
     * @return string
     */
    public function path() {
        return call_user_func_array([$this->_app, 'path'], func_get_args());
    }

    /**
     * load passthrough to app
     *
     * @see bolt\application::load
     *
     * @return self
     */
    public function load() {
        call_user_func_array([$this->_app, 'load'], func_get_args());
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
        if (is_callable($class)) {
            $inst = new browser\middleware\closure($this, $config);
            $inst->setEvent($name);
            $inst->setClosure($class);
            $name = 'closure'.microtime();
            $class = 'bolt\browser\middleware\closure';
        }
        else if (is_object($class)) {
            $inst = $class;
        }
        else if (is_callable($name)) {
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
            'methods' => array_map(function($m) {return $m->name;}, $ref->getMethods()),
        ];


        return $this;
    }


    /**
     * return a list of all middleware
     *
     * @return array
     */
    public function getMiddleware() {
        return $this->_middleware;
    }


    /**
     * pass off a run call to the app
     *
     * @return void
     */
    public function run() {
        $this->_app->run();

        return $this;
    }

    /**
     * run the browser request and send a response to the browser
     *
     * @return void
     */
    public function execute() {

        // run before we have run any router
        $this->runMiddleware('before');

        // redirect now
        if ($this->response->isRedirection() || $this->response->isReadyToSend()) {
            return $this->send();
        }

        // if we have a router
        // we need to match some routers
        if (isset($this['router'])) {
            $controller = false;

            // run the request router againts this request
            try {

                // find some route params
                $params = $this['router']->match($this->_request);

                // bind our params to request::attributes
                $this->_request->attributes->replace($params);

                // create our controller
                $controller = new $params['_controller']($this);

                // format
                if (isset($params['_format'])) {
                    $this->_request->setRequestFormat($params['_format']);
                }

            }
            catch (\Exception $e) {
                $this->_request->is404(true);
            }

            // controller
            if ($controller) {

                // run before we have run any router
                $this->runMiddleware('handle', ['controller' => $controller]);

                // run the controller
                $this->_response = $controller->run($params);


            }
            else {
                $this->runMiddleware('handle');
            }

        }
        else {
            $this->runMiddleware('handle');
        }

        // if response is now a
        if ($this->response->isRedirection() || $this->response->isReadyToSend()) {
            return $this->send();
        }

        // run before we have run any router
        $this->runMiddleware('after');

        if ($this->_request->is404() && $this->response->getContent() === "") {
            $this->response->setStatusCode(404);
            $this->response->setContent("404 - Not Found");
        }

        // send
        $this->send();

    }


    /**
     * send the response
     *
     * @return void
     */
    private function send() {
        $this->_response->prepare($this->_request);
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
        }
        $ware = $this->_middleware[$name];
        if (!$ware['instance']) {
            $this->_middleware[$ware['name']]['instance'] = $ware['instance'] = $ware['ref']->newInstance($this, $ware['config']);
        }
        return $ware['instance']->execute($method, $params);
    }

}
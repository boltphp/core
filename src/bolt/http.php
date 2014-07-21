<?php

namespace bolt;
use \b;

use \Exception;

/**
 * Base http (http) handler
 */
class http extends plugin {
    use helpers\events;

    /**
     * @var bolt\http\request
     */
    private $_request;

    /**
     * @var bolt\http\response
     */
    private $_response;

    /**
     * @var bolt\http\middleware[]
     */
    private $_middleware = [];

    /**
     * @var bolt\application
     */
    private $_app;

    /**
     * @var Exception
     */
    public $exception = false;


    /**
     * start a new http instance
     */
    public static function start($config = []) {
        $plugin = isset($config['http.plugins']) ? $config['http.plugins'] : null;
        $app = b::init($config);
        $app->plug('http', 'bolt\http');
        if (is_array($plugins)) { $app['http']->plug($plugins); }
        return $app['http'];
    }

    /**
     * Constructor.
     *
     * @param bolt\application $app Application instance
     * @param bolt\http\request $req request object
     * @param  bolt\http\response $resp response object
     *
     */
    public function __construct(application $app, http\request $req = null,  http\response $resp = null) {
        $this->_app = $app;

        // we need to
        $app->on('run:http', [$this, 'execute']);

        // new request and response
        $this->_request = $req ?: http\request::createFromGlobals();
        $this->_response = $resp ?: new http\response;

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
     * @return bolt\http\request
     */
    public function getRequest() {
        return $this->_request;
    }


    /**
     * set the response
     *
     * @param bolt\http\reqest $req new request object
     *
     * @return self
     */
    public function setRequest(\bolt\http\request $req) {
        $this->_request = $req;
        return $this;
    }


    /**
     * get the response object
     *
     * @return bolt\http\response
     */
    public function getResponse() {
        return $this->_response;
    }

    /**
     * set the response object
     *
     * @param bolt\http\response $resp new response object
     * @param bool merge merge with current repsonse object
     *
     * @return self
     */
    public function setResponse(\bolt\http\response $resp, $mergeCookies = true, $mergeHeaders = false) {
        if ($mergeCookies !== false) {
            foreach ($this->_response->headers->getCookies() as $c) {
                $resp->headers->setCookie($c);
            }
        }
        if ($mergeHeaders !== false) {
            foreach ($this->_response->headers as $name => $value) {
                if (!$resp->headers->has($name)) {
                    $resp->headers->set($name, $value);
                }
            }
        }
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
            $inst = new http\middleware\closure($this, $config);
            $inst->setEvent($name);
            $inst->setClosure($class);
            $name = 'closure'.microtime();
            $class = 'bolt\http\middleware\closure';
        }
        else if (is_object($class)) {
            $inst = $class;
        }
        else if (is_a($name, 'Closure')) {
            $class = 'bolt\http\middleware\closure';
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
     * run the http request and send a response to the http
     *
     * @return void
     */
    public function execute() {


        // run before we have run any router
        $this->runMiddleware('before');

        // redirect now
        if ($this->response->isRedirection() || $this->response->isReadyToSend()) {
            $this->runMiddleware('after');
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

                if (!class_exists($params['_controller'], true)) {
                    new $params['_controller']($this);

                    die;

                    $this->_response->setException(new \Exception("Unable to find controller {$params['_controller']}"));
                    $this->_response->readyToSend();
                    return $this->send();   
                }

                // create our controller
                $controller = new $params['_controller']($this);

                // format
                if (isset($params['_format'])) {
                    $this->_request->setRequestFormat($params['_format']);
                }

            }
            catch (\Exception $e) {
                $this->_response->setException(new \Exception($e->getMessage(), 404));
            }

            // controller
            if ($controller) {

                // run before we have run any router
                $this->runMiddleware('handle', ['controller' => $controller]);

                // run the controller
                try {
                    $this->setResponse($controller->run($params));
                }
                catch (\Exception $e) {
                    $this->_response->setException($e);
                }

            }
            else {
                $this->runMiddleware('handle');
            }

        }
        else {
            $this->runMiddleware('handle');
        }

        // run before we have run any router
        $this->runMiddleware('after');

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
            if ($this->response->isReadyToSend()) {break;}
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
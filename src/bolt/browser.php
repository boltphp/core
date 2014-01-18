<?php

namespace bolt;
use \b;

use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

/**
 * browser class
 *
 */
class browser implements \ArrayAccess {
    use \bolt\plugin;
    use \bolt\plugin\arrayAccess;

    /**
     * start a new browser app
     *
     * @param array $config app config
     *
     * @return \bolt\browser object
     */
    public static function start($config=[]) {

        $req = b::param('request', false, $config);
        $res = b::param('response', false, $config);

        // request
        $br = new browser($req, $res);

        // create a settings bucket
        b::settings('browser', []);

        // load any routes
        if (b::param('loadRoutes', true, $config) !== false) {
            b::browser('route\collectFromControllers');
        }

        // by default don't run
        if (b::param('run', false, $config) !== false) {
            $br->run();
        }

        // return the browser
        return $br;

    }


    /**
     * request object
     *
     * @see \bolt\browser\request
     */
    private $_request = false;

    /**
     * response object
     *
     * @see \bolt\browser\response
     */
    private $_response = false;

    /**
     * app routes
     */
    private $_routes = false;

    private $_middleware = [];

    private $_rootPath = false;

    private $_engines = [
        'hbr' => 'bolt\render\engine\handlebars',
        'php' => 'bolt\render\engine\php'
    ];

    /**
     * construct a new browser app
     *
     * @param \bolt\browser\request $req starting request object
     * @param \bolt\browser\response $resp starting response object
     *
     * @return self
     */
    public function __construct($req = false, $res = false) {

        // set the request and response or create new onse
        $this->setRequest($req ?: b::browser('request\createFromGlobals'));
        $this->setResponse($res ?: new browser\response());

        // routes
        $this->_routes = new browser\route\collection();

    }

    public function __call($name, $args) {

        // is this a route request
        // browser::{method}({path}, {controller}, {route})
        if (in_array(strtoupper($name), ['GET','POST','PUT','DELETE','OPTIONS'])) {
            $route = isset($args[2]) ? $args[2] : [];
            $route['path'] = $args[0];
            $route['controller'] = $args[1];
            $route['methods'] = $name;
            return call_user_func([$this, 'route'], $route);
        }

    }


    public function setRootPath($path) {
        $this->_rootPath = $path;
        return $this;
    }

    public function getRootPath() {
        return $this->_rootPath;
    }


    public function getResponse() {
        return $this->_response;
    }

    public function bind($name, $class=null, $config=[]) {
        if (is_array($name) AND $class === null) {
            array_map(function($item){
                call_user_func_array([$this, 'bind'], $item);
            }, $name);
            return $this;
        }

        $inst = false;

        // annon function
        if (is_callable($name)) {
            $class = 'bolt\browser\middleware\closure';
            $inst = new $class($config);
            $inst->setClosure($name);
            $name = 'closure'.microtime();
        }
        else {


        }

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



    /**
     * paths
     */
    public function path($name, $value=false) {
        if (is_array($name)){
            array_walk($name, function($val, $key){
                $this->path($key, $val);
            });
            return $this;
        }

        if ($name == 'root') {
            return $this->setRootPath($value);
        }

        // set in browser settings
        b::settings("browser.paths.{$name}", array_map(function($val) {
            return b::path($this->getRootPath(), $val);
        }, $value));

        return $this;
    }

    /**
     * load sub modules
     */
    public function load($class, $path) {
        if (is_array($class)) {
            array_walk($class, function($opt){
                call_user_func_array([$this, 'load'], $opt);
            });
            return $this;
        }

        b::requireFromPath($path);

//        b::load($class, $path);

        return $this;

    }

    public function setResponse(\bolt\browser\response $resp) {

        // if the request is plugable, we want a part of that
        if (property_exists($resp, 'isPlugable') AND $resp->isPlugable) {
            $resp->inherit($this);
        }

        $this->_response = $resp;
        return $this;
    }

    /**
     * set the request object
     *
     * @param \bolt\browser\request $req new request object
     *
     * @return \bolt\browser\request request object
     */
    public function setRequest(\bolt\browser\request $req) {

        // if the request is plugable, we want a part of that
        if (property_exists($req, 'isPlugable') AND $req->isPlugable) {
            $req->inherit($this);
        }

        // set our request
        $this->_request = $req;

        return $this;
    }

    /**
     * get the request object
     *
     * @return request object
     */
    public function getRequest() {
        return $this->_request;
    }

    public function route($route=[]) {
        $name = b::param('name', 'route'.rand(9,999), $route);
        $r = b::browser_route('create', $route);
        $this->_routes->add(
                $name,
                $r
            );
        return $r;
    }

    public function engine($ext, $engine) {
        $this->_engines[$ext] = b::getReflectionClass($engine);
        return $this;
    }

    public function run() {

        // loop through and add any engines not already
        foreach ($this->_engines as $ext => $ref) {
            b::render('setEngine', $ext, $ref);
        }

        // collect to backfill
        b::render('engine\collect');

        // add routes from controller classes
        b::browser('route\collection\fromControllers', $this->_routes);

        // before
        $this->_runMiddleware('before', [$this->_request]);

        // no response
        $resp = false;

        // loop through our middleware
        // and see if anyone returns a response
        // if they do, we switch a controller
        foreach ($this->_middleware as $mid) {
            $_resp = $mid['instance']->handle($this->_request, $this->_response);

            if ($_resp AND $_resp instanceof \bolt\browser\response) {
                $resp = $_resp; break;
            }

        }

        // if no response
        if (!$resp) {

            // match our route from the request
            $match = new browser\route\match($this->_routes, $this->_request->getContext());

            // we're going to try and match our request
            // if not we fall back to error
            try {
                $params = $match->matchRequest($this->_request);
            }
            catch(ResourceNotFoundException $e) {
                var_dump('bad'); die;
            }

            // bind our params to request::attributes
            $this->_request->attributes->replace($params);

            // run middle before we run the controller
            $this->_runMiddleware('before', [$this->_request]);

            // we can get started
            $controller = new $params['_controller']($this->_request, $this->_response);

            // check if we can plugin to the controller
            if (property_exists($controller, 'isPlugable') AND $controller->isPlugable) {
                $controller->inherit($this);
            }

            // build the controller
            $resp = $controller->run($this->_request->attributes->all());

        }

        if (!$resp) {
            $resp = $this->_response;
        }

        // run middle before we run the controller
        $this->_runMiddleware('handle', [$this->_request, $this->_response]);

        // prepare base on request
        $resp->prepare($this->_request);

        // figure out if content is callable
        if (is_callable($resp->getContent())) {
            while (is_callable($resp->getContent())) {
                $resp->setContent( call_user_func($resp->getContent()) );
            }
        }

        // and done
        $resp->send();

    }

     private function _runMiddleware($method, $args=[]) {
        foreach ($this->_middleware as $name => $mid) {
            if (!$mid['instance']) {
                $this->_middleware[$name]['instance'] = $mid['instance'] = $mid['ref']->newInstance();
            }
            call_user_func_array([$mid['instance'], $method], $args);
        }
    }

}
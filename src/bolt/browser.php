<?php

namespace bolt;
use \b;

use Symfony\Component\Routing\Exception\ResourceNotFoundException;

// plugin to b
b::plug('browser', '\bolt\browser');

/**
 * browser class
 *
 */
class browser {

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

    private $_rootPath = false;

    private $_paths = [];

    private $_engines = [];

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
        $this->_request = $req ?: b::browser_request('createFromGlobals');
        $this->_response = $res ?: new browser\response();

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

    public function getPath($name) {
        if (array_key_exists($name, $this->_paths)) {
            return $this->_paths[$name];
        }
        return false;
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

        $this->_paths[$name] = is_array($value) ? array_map(function($val) { return b::path($val); }, $value) : b::path($value);

        return $this;
    }

    /**
     * load sub modules
     */
    public function load($paths) {
        if (is_array($paths)) {
            array_walk($paths, function($path){
                $this->load($path);
            });
            return $this;
        }

        $try = [
            $paths,
            b::path($this->getRootPath(), $paths)
        ];

        foreach ($try as $path) {
            if (file_exists($path)) {
                b::requireFromPath($path);
                return $this;
            }
        }

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
        // defined by the user
        foreach (b::render('engine\collect') as $ext => $class) {
            if (!array_key_exists($ext, $this->_engines)) {
                $this->engine($ext, $class);
            }
        }

        // globalize our engine prefs
        b::render('setEngines', $this->_engines);

        // add routes from controller classes
        b::browser('route\collection\fromControllers', $this->_routes);

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

        // we can get started
        $controller = new $params['_controller']($this->_request, $this->_response);

        // build the controller
        $resp = $controller->run($params);

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

}
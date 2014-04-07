<?php

namespace bolt\http;
use \b;

/// depend
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * router manager
 */
class router {

    /**
     * @var bolt\router\collection
     */
    private $_collection;

    /**
     * @var bolt\http
     */
    private $_http;

    /**
     * @var array
     */
    private $_config;



    /**
     * Constructor
     *
     * @param bolt\application $app
     */
    public function __construct(\bolt\http $http, $config = []) {
        $this->_http = $http;
        $this->_collection = new router\collection();
        $this->_config = $config;
    }


    /**
     * get collection
     *
     * @return bolt\router\collection
     */
    public function getCollection() {
        return $this->_collection;
    }

    /**
     * magic call
     *
     * @param string $name
     * @param array $args
     *
     * @return mix
     */
    public function __call($name, $args) {


        // one of our methods
        if (in_array(strtolower($name), ['get','post','put','delete','head'])) {
            $r = new router\route($args[0]);
            $r->setController($args[1]);
            $this->add($r);
            return $r;
        }

    }


    /**
     * add a route to the toplevel collection
     *
     * @param bolt\http\router\route $route
     *
     * @return self
     */
    public function add(\bolt\http\router\route $route) {
        $this->_collection->add($route->getName(), $route);
        return $this;
    }

    public function getByName($name) {
        return $this->_collection->get($name);
    }

    /**
     * match a request to defined routes
     *
     * @param bolt\http\request $req
     * @param string $path
     *
     * @return array
     */
    public function match(\bolt\http\request $req, $path = null) {

        // matcher
        $matcher = new UrlMatcher($this->_collection, $req->getContext());

        // try to match the request
        try {
            $params = $path !== null ? $matcher->match($path) : $matcher->matchRequest($req);
        }
        catch(ResourceNotFoundException $e) {

            // no match found for collections
            // try to match agaist our fallback
            if (isset($this->_config['fallback']) && is_a($this->_config['fallback'], '\bolt\http\router\route')) {
                return $this->_tryFallback($req, $path);
            }

            // trow an error
            throw new \Exception("No route match found");

        }


        return $params;

    }


    /**
     * try the registered callback route
     *
     * @param bolt\http\request $req
     * @param string $path
     *
     * @return void
     */
    private function _tryFallback(\bolt\http\request $req, $path = null) {

        // collection of one
        $co = new router\collection();
        $route = $this->_config['fallback'];

        $co->add($route->getName(), $route);

        // matcher
        $matcher = new UrlMatcher($co, $req->getContext());

        // try to match the request
        try {
            $params = $path !== null ? $matcher->match($path) : $matcher->matchRequest($req);
        }
        catch(\ResourceNotFoundException $e) {
            throw new \Exception("Unable to match fallback route.");
        }



        return $params;


    }

    /**
     * load all routes that are defined in controllers
     * that exend bolt\http\router\face
     *
     * @return void
     */
    public function loadFromControllers(array $paths = []) {
        $routes = []; $files = [];

        //
        foreach ($paths as $path) {
            if (is_object($path) && method_exists($path, 'asArray')) {
                $files = array_merge($files, $path->asArray());
            }
            else if (is_a($path, 'bolt\helpers\fs\file')) {
                $files[] = (string)$file;
            }
            else if (is_array($path)) {
                $files = array_merge($files, $path);
            }
            else {
                $files[] = $this->_http->find($path);
            }
        }

        foreach ($files as $file) {
            require_once $file;
        }

        // get all loaded classes
        if (($classes = b::getClassImplements('\bolt\http\router\face')) != false) {
            foreach ($classes as $class) {
                if (
                    $class->name === 'bolt\http\controller' ||
                    $class->name === 'bolt\http\router\route' ||
                    (!$class->hasProperty('routes') && !$class->hasMethod('getRoutes'))
                ) {continue;} // skip our controller class and make sure we have at least $routes || getRoutes()

                $_ = [
                    'ref' => $class,
                    'collection' => $class->hasProperty('routeCollection') ? $class->getStaticPropertyValue('routeCollection') : ['prefix' => ''],
                    'routes' => []
                ];

                if ($class->hasProperty('routes')) {
                    $_['routes'] = array_merge($_['routes'], $class->getStaticPropertyValue('routes'));
                }

                if ($class->hasMethod('getRoutes')) {
                    $name = $class->name;
                    $_['routes'] = array_merge($_['routes'], $name::getRoutes());
                }

                $routes[] = $_;

            }
        }

        foreach ($routes as $class) {
            $c = new router\collection();

            // loop through routes
            foreach ($class['routes'] as $key => $route) {

                $name = b::param('name', false, $route);


                if (!$name && !is_string($key)) {
                    $name = "route".rand(9, 999);
                }
                else if (!$name) {
                    $name = $key;
                }

                // add our two default things
                $route['controller'] = $class['ref']->name;
                $route['action'] = b::param('action', false, $route);

                if (!isset($route['formats']) AND isset($class['collection']['formats'])) {
                    $route['formats'] = $class['collection']['formats'];
                }

                // loop through each part and set it
                $c->add($name, router\route::create($route) );

            }

            // get our prefix vars
            extract(
                    array_merge(['prefix' => '', 'requirements' => [], 'options' => [], 'host' => false, 'schemes' => []], $class['collection']),
                    EXTR_OVERWRITE
                );

            // set prefix
            $c->addPrefix($prefix, $requirements, $options, $host, $schemes);

            // add this collection
            $this->_collection->addCollection($c);

        }

    }


}
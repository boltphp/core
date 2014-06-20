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

        // do we have compiled controllers
        $compiled = $http->app->getCompiled('router');

        if ($compiled) {
            $this->_collection = $compiled['data']['collection'];
        }
        else if (isset($config['autoload']) && $config['autoload'] === true) {
            if (!isset($config['dirs'])) {
                throw new \Exception("Called autoload with no directories");
            }
            foreach ($config['dirs'] as $dir) {
                b::requireFromPath($this->_http->path($dir));
            }
            $this->loadFromControllers();
        }


        // compile
        $http->app->on('compile', [$this, 'onCompile']);

    }

    public function onCompile($e) {
        $dirs = $this->_config['dirs'];

        if (count($dirs) == 0) {return false;}

        $collection = new router\collection();

        foreach ($dirs as $dir) {
            foreach (b::getRegexFiles($this->_http->path($dir)) as $file) {
                require_once $file;
            }
        }

        $classes = $this->loadFromControllers($collection);

        //
        $e->data['client']->saveCompileLoader('router', [
            'collection' => $collection,
            'controllers' => array_map(function($class){
                return $class->name;
            }, $classes)
        ]);

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
    public function loadFromControllers(router\collection $collection = null) {
        $collection = $collection ?: $this->_collection;
        $classes = false;
        $controllers = [];

        // get all loaded classes
        if (($classes = get_declared_classes()) != false) {

            foreach ($classes as $class) {

                if ( $class === 'bolt\http\controller' || $class === 'bolt\http\router\route' || $class === 'bolt\http\controller\route') {continue;} // skip our controller class and make sure we have at least $routes || getRoutes()
                if ( !is_subclass_of($class, '\bolt\http\router\face')) {continue;}
                $class = new \ReflectionClass($class);

                if (!$class->hasProperty('routes') && !$class->hasMethod('getRoutes')) {continue;}

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

                $controllers[] = $class;

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
            $collection->addCollection($c);

        }


        return $controllers;

    }


}
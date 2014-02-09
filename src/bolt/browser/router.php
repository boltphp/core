<?php

namespace bolt\browser;
use \b;

/// depend
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

/**
 * router manager
 */
class router {

    /**
     * @var bolt\router\collection
     */
    private $_collection;

    /**
     * @var bolt\browser
     */
    private $_browser;


    /**
     * Constructor
     *
     * @param bolt\application $app
     */
    public function __construct(\bolt\browser $browser) {
        $this->_browser = $browser;
        $this->_collection = new router\collection();
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
     * @param bolt\browser\router\route $route
     *
     * @return self
     */
    public function add(\bolt\browser\router\route $route) {
        $this->_collection->add($route->getName(), $route);
        return $this;
    }

    public function getByName($name) {
        return $this->_collection->get($name);
    }

    /**
     * match a request to defined routes
     *
     * @param bolt\browser\request $req
     *
     * @return array
     */
    public function match(\bolt\browser\request $req) {

        // matcher
        $matcher = new UrlMatcher($this->_collection, $req->getContext());

        // try to match the request
        try {
            $params = $matcher->matchRequest($req);
        }
        catch(ResourceNotFoundException $e) {
            var_dump('bad'); die;
        }

        return $params;

    }


    /**
     * load all routes that are defined in controllers
     * that exend bolt\browser\router\face
     *
     * @return void
     */
    public function loadFromControllers() {
        $routes = [];

        // get all loaded classes
        if (($classes = b::getClassImplements('\bolt\browser\router\face')) != false) {
            foreach ($classes as $class) {
                if (
                    $class->name === 'bolt\browser\controller' OR
                    (!$class->hasProperty('routes') AND !$class->hasMethod('getRoutes'))
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

                if (!$name AND !is_string($key)) {
                    $name = "route".rand(9, 999);
                }
                else if (!$name) {
                    $name = $key;
                }

                // no name
                unset($route['name']);

                // add our two default things
                $route['controller'] = $class['ref']->name;
                $route['action'] = b::param('action', false, $route);

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
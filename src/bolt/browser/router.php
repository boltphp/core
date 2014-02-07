<?php

namespace bolt\browser;
use \b;

use Symfony\Component\Routing\Matcher\UrlMatcher;

use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class router {


    private $_collection;

    private $_app;

    public function __construct(\bolt\application $app) {
        $this->_app = $app;

        $this->_collection = new router\collection();

    }

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

        return null;
    }


}
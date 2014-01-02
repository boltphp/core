<?php

namespace bolt\browser\route;
use \b;

use Symfony\Component\Routing\RouteCollection;

class collection extends RouteCollection {

    public static function create($routes=[]) {
        $c = new collection();
        array_map(function($route) use ($c){ $c->add($route->getName(), $route); }, $routes);
        return $c;
    }

    public static function fromControllers(\bolt\browser\route\collection $collection) {
        $routes = [];

        // get all loaded classes
        if (($classes = b::getClassImplements('\bolt\browser\route\face')) != false) {
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
            $c = new collection();

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
                $c->add($name,  b::browser_route('create', $route) );

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

        return null;
    }


}
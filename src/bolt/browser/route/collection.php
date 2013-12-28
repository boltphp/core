<?php

namespace bolt\browser\route;
use \b;

use Symfony\Component\Routing\RouteCollection;

class collection extends RouteCollection {

    public static function fromControllers(\bolt\browser\route\collection $collection) {
        $routes = [];

        // get all loaded classes
        foreach (b::getClassImplements('\bolt\browser\route\face') as $class) {
            if (
                $class->name === 'bolt\browser\controller' OR
                (!$class->hasProperty('routes') AND !$class->hasMethod('getRoutes'))
            ) {continue;} // skip our controller class and make sure we have at least $routes || getRoutes()

            $routes[$class->name] = [];

            if ($class->hasProperty('routes')) {
                $routes[$class->name] = array_merge($routes[$class->name], $class->getStaticPropertyValue('routes'));
            }

            if ($class->hasMethod('getRoutes')) {
                $name = $class->name;
                $routes[$class->name] = array_merge($routes[$class->name], $name::getRoutes());
            }

        }



        foreach ($routes as $class => $items) {
            foreach ($items as $key => $route) {

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
                $route['controller'] = $class;
                $route['action'] = b::param('action', false, $route);

                // loop through each part and set it
                $collection->add($name,  b::browser_route('create', $route) );

            }

        }

        return null;
    }


}
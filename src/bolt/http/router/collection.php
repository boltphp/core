<?php

namespace bolt\http\router;
use \b;

use Symfony\Component\Routing\RouteCollection;


/**
 * collection of routes
 */
class collection extends RouteCollection {

    /**
     * static create route collection
     *
     * @param array $routes
     *
     * @return bolt\http\router\collection
     */
    public static function create($routes=[]) {
        $c = new collection();
        array_map(function($route) use ($c){ $c->add($route->getName(), $route); }, $routes);
        return $c;
    }

    public static function __set_state($state) {
        $c = new collection();
        foreach ($state['routes'] as $name => $route) {
            $c->add($name, $route);
        }
        return $c;
    }

}
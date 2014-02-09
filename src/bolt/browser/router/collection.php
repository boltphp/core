<?php

namespace bolt\browser\router;
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
     * @return bolt\browser\router\collection
     */
    public static function create($routes=[]) {
        $c = new collection();
        array_map(function($route) use ($c){ $c->add($route->getName(), $route); }, $routes);
        return $c;
    }


}
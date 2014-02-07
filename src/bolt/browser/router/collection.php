<?php

namespace bolt\browser\router;
use \b;

use Symfony\Component\Routing\RouteCollection;

class collection extends RouteCollection {

    public static function create($routes=[]) {
        $c = new collection();
        array_map(function($route) use ($c){ $c->add($route->getName(), $route); }, $routes);
        return $c;
    }


}
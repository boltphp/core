<?php

namespace bolt\browser\controller;
use \b;

class closure extends \bolt\browser\controller\route {

    public function build($params=[]) {

        $func = $params['_closure'];

        // ref
        $ref = new \ReflectionFunction($func);

        // get
        $args = $this->getArgsFromMethodRef($ref, $params);

        // return our resp
        return call_user_func_array($func->bindTo($this), $args);

    }

}
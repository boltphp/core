<?php

namespace bolt\browser\controller;
use \b;


/**
 * closure controller class
 */
class closure extends \bolt\browser\controller\route {

    /**
     * build the closure controller
     *
     * @param array $params
     *
     * @return mixed
     */
    public function build($params=[]) {

        if (!isset($params['_closure'])) {
            throw new \Exception('No closure provided');
            return;
        }

        $func = $params['_closure'];

        if (!is_a($func, 'Closure')) {
            throw new \Exception('Class is not a closure');
            return;
        }

        // ref
        $ref = new \ReflectionFunction($func);

        // get
        $args = $this->getArgsFromMethodRef($ref, $params);

        // return our resp
        return call_user_func_array($func->bindTo($this), $args);

    }

}
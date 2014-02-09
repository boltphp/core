<?php

namespace bolt\browser\middleware;
use \b;

use \Closure as cc;


/**
 * closure middleware holder
 */
class closure extends \bolt\browser\middleware {

    /**
     * @var Closure
     */
    private $_closure = false;

    /**
     * set the middleware closure
     *
     * @param Closure $closure
     *
     * @return self
     */
    public function setClosure(\Closure $closure) {
        $this->_closure = $closure;
        return $this;
    }


    /**
     * handle the middleware request
     *
     * @param array $args
     *
     * @return mixed
     */
    public function handle($args) {
        $ref = new \ReflectionFunction($this->_closure);
        return call_user_func_array(cc::bind($this->_closure, $this->browser), $this->getArgsFromMethodRef($ref, $args));
    }

}
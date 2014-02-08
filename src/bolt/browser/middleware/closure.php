<?php

namespace bolt\browser\middleware;
use \b;

use \Closure as cc;

class closure extends \bolt\browser\middleware {

    private $_closure = false;

    public function setClosure($closure) {
        $this->_closure = $closure;
    }

    public function handle($args) {
        $ref = new \ReflectionFunction($this->_closure);
        return call_user_func_array(cc::bind($this->_closure, $this->browser), $this->getArgsFromRef($ref, $args));
    }

}
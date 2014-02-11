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
     * @var string
     */
    private $_event = 'handle';


    /**
     * set the event to fire middleware
     *
     * @param string $event
     *
     * @return self
     */
    public function setEvent($event) {
        $this->_event = $event;
        return $this;
    }


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
     * execute the callback
     *
     * @param string $event event that was fired
     * @param array $args
     *
     * @return mixed
     */
    private function _execute($event, $args) {
        if ($event !== $this->_event) {return;}
        $ref = new \ReflectionFunction($this->_closure);
        return call_user_func_array(cc::bind($this->_closure, $this->browser), $this->getArgsFromMethodRef($ref, $args));
    }


    /**
     * before middleware event
     *
     * @param array $args
     *
     * @return mixed
     */
    public function before($args = []) {
        return $this->_execute('before', $args);
    }


    /**
     * handle the middleware request
     *
     * @param array $args
     *
     * @return mixed
     */
    public function handle($args = []) {
        return $this->_execute('handle', $args);
    }

    /**
     * after middleware event
     *
     * @param array $args
     *
     * @return mixed
     */
    public function after($args = []) {
        return $this->_execute('after', $args);
    }

}
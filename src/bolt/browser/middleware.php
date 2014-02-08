<?php

namespace bolt\browser;
use \b;

use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

abstract class middleware {

    protected $browser = false;

    protected $config = false;

    final public function __construct(\bolt\browser $browser, $config=[]) {
        $this->browser = $browser;
        $this->config = $config;
        $this->init();
    }


    final public function setBrowser($browser){
        $this->browser = $browser;
    }

    final public function matchRoute(\bolt\browser\route $route, \bolt\browser\request $req) {

        $collection = b::browser('route\collection\create', [$route]);
        $match = new UrlMatcher($collection, $req->getContext());

        // we're going to try and match our request
        // if not we fall back to error
        try {
            return $match->matchRequest($req);
        }
        catch(ResourceNotFoundException $e) {
            return false;
        }

    }

    public function init() {

    }

    final public function execute($method, $args) {

        // get the method ref
        $ref =  b::getReflectionClass($this)->getMethod($method);

        // call it
        return call_user_func_array([$this, $method], $this->getArgsFromRef($ref, $args));

    }

    protected function getArgsFromRef($ref, $params) {
        $args = [];

        foreach ($ref->getParameters() as $param) {
            $name = $param->getName();

            if ($param->getClass()) {

                if ($param->getClass()->name === 'bolt\browser\request') {
                    $args[] = $this->browser->request;
                }
                else if ($param->getClass()->name === 'bolt\browser\response') {
                    $args[] = $this->browser->response;
                }
                else if ($param->getClass()->name === 'bolt\browser') {
                    $args[] = $this->browser;
                }
                else if ($param->getClass()->name === 'bolt\application') {
                    $args[] = $this->browser->app;
                }

            }
            else if ($name == 'request' OR $name == 'req') {
                $args[] =  $this->browser->request;
            }
            else if ($name == 'response' OR $name == 'resp') {
                $args[] =  $this->browser->response;
            }
            else if ($name == 'browser'){
                $args[] =  $this->browser;
            }
            else if ($name == 'app') {
                $args[] =  $this->browser->app;
            }
            else if ($name === 'args') {
                $args[] = $params;
            }
            else if (array_key_exists($name, $params)) {
                $args[] = $params[$name];
            }
            else if ($param->isOptional()) {
                $args[] = $param->getDefaultValue();
            }
            else if ($param->isOptional()) {
                $args[] = null;
            }

        }

        return $args;
    }

}
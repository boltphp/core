<?php

namespace bolt\browser;
use \b;

use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

abstract class middleware {

    protected $browser = false;

    protected $config = false;

    final public function __construct($config=[]) {
        $this->config = b::bucket('create', $config);
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

    public function before($req) {
        return false;
    }

    public function handle($req, $res) {
        return false;
    }

    public function after($res) {
        return false;
    }

}
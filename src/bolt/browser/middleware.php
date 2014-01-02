<?php

namespace bolt\browser;
use \b;

abstract class middleware {

    protected $browser = false;

    protected $config = false;

    final public function __construct($config=[]) {
        $this->config = b::bucket('create', $config);
    }


    final public function setBrowser($browser){
        $this->browser = $browser;
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
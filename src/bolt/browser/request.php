<?php

namespace bolt\browser;
use \b;


use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\Routing\RequestContext as SymfonyRequestContext;

class request extends SymfonyRequest {

    private $_bguid = false;

    public function __construct() {
        $this->_bguid = "bguid".microtime(true);
        call_user_func_array([get_parent_class(), '__construct'], func_get_args());
    }

    public function bguid() {
        return $this->_bguid;
    }

    public function getContext() {
        $ctx = new SymfonyRequestContext();
        $ctx->fromRequest($this);
        return $ctx;
    }

}
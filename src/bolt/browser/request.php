<?php

namespace bolt\browser;
use \b;

/// require symfony request and response
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\Routing\RequestContext as SymfonyRequestContext;

/**
 * request class
 */
class request extends SymfonyRequest {

    /**
     * Constructor.
     */
    public function __construct() {
        call_user_func_array([get_parent_class(), '__construct'], func_get_args());
    }

    /**
     * get the request context
     *
     * return Symfony\Component\Routing\RequestContext
     */
    public function getContext() {
        $ctx = new SymfonyRequestContext();
        $ctx->fromRequest($this);
        return $ctx;
    }

}
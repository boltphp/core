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
     * @var bool
     */
    private $_is404 = false;

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

    /**
     * is the request currently in a 404 state
     *
     * @param bool $flag
     *
     * @return mixed
     */
    public function is404($flag = null) {
        return $flag === null ? $this->_is404 : $this->_is404 = $flag;
    }


}
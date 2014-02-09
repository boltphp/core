<?php

namespace bolt\browser;

/// require symfony response
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * response object
 */
class response extends SymfonyResponse {

    /**
     * Constructor
     */
    public function __construct() {
        call_user_func_array([get_parent_class(), '__construct'], func_get_args());
    }

}
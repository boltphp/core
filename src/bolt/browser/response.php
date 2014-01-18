<?php

namespace bolt\browser;
use \b;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class response extends SymfonyResponse {
    use \bolt\plugin;

    private $_bguid = false;

    public function __construct() {
        $this->_bguid = "bguid".microtime(true);
        call_user_func_array([get_parent_class(), '__construct'], func_get_args());
    }

    public function bguid() {
        return $this->_bguid;
    }

    public function useLayout() {
        return true;
    }

}
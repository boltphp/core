<?php

namespace bolt\browser\middleware;
use \b;

class session extends \bolt\browser\middleware {

    private $_provider = false;

    public function init() {
        $this->_provider = $this->config->value('provider');
    }

    public function before($req) {

    }

}
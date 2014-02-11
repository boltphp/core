<?php

namespace bolt\browser\response;
use \b;

class redirect extends \bolt\browser\response {

    private $_url;

    public function __construct($url = false, $code = 301) {
        $this->_url = $url;
        parent::__construct();

        // what code
        $this->setStatusCode($code);

        // header
        if ($url) {
            $this->setUrl($url);
        }

    }

    public function setUrl($url) {
        $this->headers->set('Location', $url);
        return $this;
    }

}
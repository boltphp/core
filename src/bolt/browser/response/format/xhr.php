<?php

namespace bolt\browser\response\format;
use \b;

class xhr extends \bolt\browser\response\format {

    public $contentType = 'application/json';

    public function format($content) {
        return json_encode($content);
    }

}
<?php

namespace bolt\http\response\format;
use \b;

class xhr extends \bolt\http\response\format {

    public $contentType = 'application/json';

    public function format($content) {
        return json_encode($content);
    }

}
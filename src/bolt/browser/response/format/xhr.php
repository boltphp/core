<?php

namespace bolt\browser\response\format;
use \b;

class xhr extends \bolt\browser\response\format {

    public $contentType = 'application/json';

    public function format($content) {

        var_dump($content); die;

        return json_encode($content);
    }

}
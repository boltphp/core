<?php

namespace bolt\http\response\format;
use \b;

class json extends \bolt\http\response\format {

    public $contentType = 'application/json';

    public function format($content) {
        if (is_resource($content)) {
            throw new \Exception("Can not json_encode resource");
        }
        return json_encode($content);
    }

}
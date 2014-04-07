<?php

namespace bolt\http\response\format;
use \b;

class js extends \bolt\http\response\format {

    public $contentType = 'text/javascript';

    // public function format($content) {
    //     if (is_resource($content)) {
    //         throw new \Exception("Can not json_encode resource");
    //     }
    //     return json_encode($content);
    // }

}
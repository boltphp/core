<?php

namespace bolt\browser\response\format;
use \b;

class xml extends \bolt\browser\response\format {

    public $contentType = 'application/xml';

    public function format($content) {
        if (is_a($content, 'bolt\render\xmlGenerator')) {
            return $content->render();
        }
        $r = new \bolt\render\xml($content);
        return $content->render();

    }

}
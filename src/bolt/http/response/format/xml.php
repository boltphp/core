<?php

namespace bolt\http\response\format;
use \b;

class xml extends \bolt\http\response\format {

    public $contentType = 'application/xml';

    public function format($content) {
        if (is_a($content, 'bolt\render\xml\generat')) {
            return $content->render();
        }
        else if (is_array($content, 'DOMDocument')) {
            return $content->saveHTML();
        }
        else if (is_string($content)) {
            return $content;
        }
        $r = new \bolt\render\xml($content);
        return $content->render();
    }

}
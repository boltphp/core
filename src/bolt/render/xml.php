<?php

namespace bolt\render;
use \b;

use \DOMDocument, \DOMAttr, \DOMCDATASection;

/**
 * render a dom string
 */
class xml extends base {


    /**
     * compile the dom doc to serializeable object
     */
    public function compile() {


    }


    /**
     * render an html string into a dom doc
     *
     * @param string array $data
     * @param array $vars
     *
     * @return xmlGenerator
     */
    public function render($data, $vars = []) {
        $xml = new xml\generate($data);
        return $xml;
    }


}

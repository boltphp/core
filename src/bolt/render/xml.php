<?php

namespace bolt\render;
use \b;

/**
 * render a dom string
 */
class xml extends base {

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

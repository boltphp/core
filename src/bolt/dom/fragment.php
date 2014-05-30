<?php

namespace bolt\dom;
use \b;

use HTML5;

class fragment extends document {

    public function __construct($charset = 'UTF-8', $html = null) {
        parent::__construct($charset);
        if ($html) {
            $this->setHTML($html);
        }
    }

    public function setHTML($html) {
        $dom = HTML5::loadHTMLFragment($html);
        $wrap = parent::createElementNative('div');
        $wrap->setAttribute('id', $this->refid);
        $wrap->appendChild($this->importNode($dom, true));
        $this->appendChild($wrap);
        return $this;
    }

    public function getHTML($el = null) {
        $html = "";
        foreach ($this->children() as $node) {
            switch ($node->nodeType) {
                case XML_TEXT_NODE:
                    $html .= $node->nodeValue; break;
                default:
                    $html .= parent::saveHTML($node->element);
            };
        }
        return $html;
    }

    public function saveHTML($el = null) {
        return $this->getHTML();
    }

    public function children() {
        return $this->xpath(sprintf("div[@id = '%s']/node()", $this->refid), $this);
    }

    public function find($selector, $element = NULL) {
        return parent::find(sprintf("#%s %s", $this->refid, $selector), $this);
    }

}

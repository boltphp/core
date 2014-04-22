<?php

namespace bolt\dom;


use Symfony\Component\CssSelector\CssSelector;

class element extends node {

    private $_dom;

    public $tagName = 'div';

    public function __construct($tag = null, $value = "", $attr = []) {
        $this->_dom = new \bolt\dom();

        $tag = $tag ?: $this->tagName;

        $node = $this->_dom->doc()->createElement($tag);
        $this->_dom->doc()->appendChild($node);

        parent::__construct($this->_dom, $node);

        $this->html($value);
        $this->attr($attr);

        $this->init();

    }

    public function html($html = null){
        if ($html !== null) {
            return parent::html($html);
        }
        else {

            $ref = clone $this->_dom->doc();

            $xpath = new \DOMXPath($ref);

            foreach ($xpath->query(CssSelector::toXPath('*[data-domref]')) as $node) {
                $node->removeAttribute('data-domref');
            }
            foreach ($xpath->query(CssSelector::toXPath('*[data-fragmentref]')) as $node) {
                $node->removeAttribute('data-fragmentref');
            }

            return $ref->saveHTML();

        }
    }

}
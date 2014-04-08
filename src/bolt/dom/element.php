<?php

namespace bolt\dom;

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

}
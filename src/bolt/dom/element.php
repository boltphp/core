<?php

namespace bolt\dom;

class element extends node {

    private $_dom;

    public function __construct($tag, $value = "", $attr = []) {
        $this->_dom = new \bolt\dom();
        $node = $this->_dom->doc()->createElement($tag);
        $this->_dom->doc()->appendChild($node);

        parent::__construct($this->_dom, $node);

        $this->html($value);
        $this->attr($attr);


    }

}
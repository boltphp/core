<?php

namespace bolt\dom;
use \b;

use DOMElement,
    DOMAttr;

class element {

    public $ownerDocument;

    public $element;

    public $guid;

    public function __construct($tag = null, $value = "", $attr = [], document $document = null) {
        $this->ownerDocument = $document ?: new document();
        $this->element = $this->ownerDocument->createElement($tag, $value);

        $this->guid = b::guid("noderef");

        $this->attr('data-domref', $this->guid);

        if (is_array($attr)) {
            $this->attr($attr);
        }

    }

    public function __get($name) {
        return $this->attr($name);
    }

    public function __set($name, $value) {
        return $this->attr($name, $value);
    }

    public function __call($name, $args) {
        return call_user_func_array([$this->element, $name], $args);
    }


    public function html($html = null) {
        if ($html) {

        }
        else {
            return $this->ownerDocument->getHTML($this);
        }
    }

    public function find($selector) {
        return $this->ownerDocument->find(sprintf('*[data-domref="%s"] %s', $this->_guid, $selector));
    }

    public function attr($name, $value = null) {
        if (is_array($name)) {
            foreach ($name as $n => $v) {
                if (is_numeric($n)) {
                    $this->appendChild(new DOMAttr($v));
                }
                else {
                    $this->setAttribute($n, html_entity_decode($v, ENT_QUOTES, 'utf-8'));
                }
            }
            return $this;
        }

        if ($value !== null) {
            $this->setAttribute($name, $value);
            return $this;
        }
        else {
            return $this->getAttribute($name);
        }
    }

}
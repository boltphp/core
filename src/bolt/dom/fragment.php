<?php

namespace bolt\dom;
use \b;

class fragment implements \ArrayAccess {

    private $_dom = false;
    public $root = false;

    private $_guid;

    public function __construct($tag, $attr=[]) {
        $this->_dom = new \bolt\dom();

        $this->_guid = b::guid('fragment');

        $this->_dom->doc()->loadHTML('<'.$tag.' data-fragmentref="'.$this->_guid.'"></'.$tag.'>');

        $this->root = $this['[data-fragmentref="'.$this->_guid.'"]'];


    }

    public function guid() {
        return $this->root->guid();
    }

    public function reset(\bolt\dom $dom, $node) {
        $this->_dom = $dom;
        $this->root->reset($dom, $node);
    }

    public function html() {
        return $this->_dom->doc()->saveHTML($this->root->node());
    }

    public function __call($name, $args) {
        return call_user_func_array([$this->_dom, $name], $args);
    }


    public function offsetExists($name) {
        return $this->_dom->offsetExists($name);
    }

    public function offsetGet($name) {
        return $this->_dom->offsetGet($name);
    }

    public function offsetSet($name, $value) {
        return $this->_dom->offsetSet($name, $value);
    }

    public function offsetUnset($name) {
        return $this->_dom->offsetUnset($name);
    }

    public function __toString() {
        return $this->html();
    }

}
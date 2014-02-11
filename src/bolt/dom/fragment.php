<?php

namespace bolt\dom;

class fragment implements \ArrayAccess {

    private $_dom = false;
    public $root = false;

    public function __construct($tag, $attr=[]) {
        $this->_dom = new \bolt\dom();

        // root node
        $this->root = $this->create($tag, '', $attr);

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
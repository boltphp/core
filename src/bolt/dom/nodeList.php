<?php

namespace bolt\dom;



class nodeList extends \SplObjectStorage {

    private $_dom;

    public function __construct($dom) {
        $this->_dom = $dom;
    }

    public function __call($name, $args) {
        if (count($this) == 1) {
            return call_user_func_array([$this->first(), $name], $args);
        }
        foreach ($this as $node) {
            call_user_func_array([$node, $name], $args);
        }
    }

    public function item($pos) {
        foreach ($this as $i => $node) {
            if ($i == $pos) {return $node;}
        }
        return false;
    }

    public function first() {
        return $this->item(0);
    }

    public function last() {
        return $this->item(count($this) - 1);
    }

    public function size() {
        return count($this);
    }

    public function html() {
        $html = '';

        foreach ($this as $item) {
            $html .= $item->html();
        }

        return $html;

    }

    public function __toString() {
        return (string)$this->html();
    }

}
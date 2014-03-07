<?php

namespace bolt\helpers;

use \Closure;

class collection implements \ArrayAccess, \Countable, \IteratorAggregate, \JsonSerializable {

    private $_items = [];

    public function each(Closure $cb, $data = []) {
        foreach ($this->_items as $key => $item) {
            call_user_func($cb, $item, $key, $data);
        }
        return $this;
    }

    public function first() {
        return count($this) > 0 ? reset($this->_items) : null;
    }

    public function last() {
        return count($this) > 0 ? end($this->_items) : null;
    }

    public function filter(Closure $cb) {
        $this->_items = array_filter($this->_items, $cb);
        return $this;
    }


    public function push($item) {
        $this->_items[] = $item;
        return $this;
    }

    public function shift() {
        array_shift($this->_items);
        return $this;
    }

    public function unshift($item) {
        array_unshift($this->_items, $item);
        return $this;
    }

    public function getIterator() {
        return new \ArrayIterator($this->_items);
    }

    public function count() {
        return count($this->_items);
    }

    public function jsonSerialize() {
        return json_encode($this->_items);
    }

    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->_items[] = $value;
        }
        else {
            $this->_items[$offset] = $value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->_items[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->_items[$offset]);
    }

    public function offsetGet($offset) {
        return isset($this->_items[$offset]) ? $this->_items[$offset] : null;
    }


}
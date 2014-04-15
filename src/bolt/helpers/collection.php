<?php

namespace bolt\helpers;

use \Closure;

class collection implements \ArrayAccess, \Countable, \IteratorAggregate, \JsonSerializable {

    protected $items = [];

    public function each(Closure $cb, $data = []) {
        foreach ($this->items as $key => $item) {
            call_user_func($cb, $item, $key, $data);
        }
        return $this;
    }

    public function first() {
        return count($this) > 0 ? reset($this->items) : null;
    }

    public function last() {
        return count($this) > 0 ? end($this->items) : null;
    }

    public function filter(Closure $cb) {
        $this->items = array_filter($this->items, $cb);
        return $this;
    }

    public function splice($offset, $length, $replace = []) {
        array_splice($this->items, $offset, $length, $replace);
        return $this;
    }

    public function map(Closure $cb) {
        $this->items = array_map($cb, $this->items);
        return $this;
    }

    public function push($item) {
        $this->items[] = $item;
        return $this;
    }

    public function shift() {
        array_shift($this->items);
        return $this;
    }

    public function unshift($item) {
        array_unshift($this->items, $item);
        return $this;
    }

    public function getIterator() {
        return new \ArrayIterator($this->items);
    }

    public function count() {
        return count($this->items);
    }

    public function jsonSerialize() {
        return json_encode($this->items);
    }

    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->items[] = $value;
        }
        else {
            $this->items[$offset] = $value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->items[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->items[$offset]);
    }

    public function offsetGet($offset) {
        return isset($this->items[$offset]) ? $this->items[$offset] : null;
    }


}
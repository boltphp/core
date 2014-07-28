<?php

namespace bolt\helpers;
use b, Closure;


/**
 * Collection
 * 
 */
class collection implements \ArrayAccess, \Countable, \IteratorAggregate, \JsonSerializable {

    /**
     * items in the collection
     * 
     * @var array
     */
    protected $items = [];

    /**
     * Constructor
     * 
     * @param array $items
     */
    public function __construct(array $items = []) {
        $this->items = $items;
    }


    /**
     * perform callback on each item in the array
     * 
     * @param  string|Closure $cb   closure to execute or name of function
     * @param  array $data
     * 
     * @return self
     */
    public function each($cb, array $data = []) {
        if (is_string($cb)) { $cb = [$this, $cb]; }
        foreach ($this->items as $key => &$item) {
            call_user_func($cb, $item, $key, $data, $this);
        }
        return $this;
    }


    /**
     * return first $item
     * 
     * @return mixed
     */
    public function first() {
        return count($this) > 0 ? reset($this->items) : null;
    }


    /**
     * return last $item
     * 
     * @return mixed
     */
    public function last() {
        return count($this) > 0 ? end($this->items) : null;
    }


    /**
     * filter $items by callback
     * @see php::array_filter
     * 
     * @param  Closure $cb    
     * 
     * @return self
     */
    public function filter(Closure $cb) {
        $this->items = array_filter($this->items, $cb);
        return $this;
    }


    /**
     * splice 
     * @see  php::array_splice
     * 
     * @param  integer $offset 
     * @param  integer $length  
     * @param  array $replace 
     * 
     * @return self
     */
    public function splice($offset, $length, array $replace = []) {
        array_splice($this->items, $offset, $length, $replace);
        return $this;
    }


    /**
     * slice
     * @see  php::array_slice
     * 
     * @param  integer  $offset        
     * @param  integer  $length        
     * @param  boolean $preserve_keys 
     * 
     * @return self
     */
    public function slice($offset, $length = null, $preserve_keys = true) {
        $this->items = array_slice($this->items, $offset, $length, $preserve_keys);
        return $this;
    }


    /**
     * map
     * @see  php::array_map
     * 
     * @param  Closure $cb
     * 
     * @return self
     */
    public function map(Closure $cb) {
        $this->items = array_map($cb, $this->items);
        return $this;
    }


    /**
     * diff
     * @see  php::array_diff
     *
     * @param ...
     * 
     * @return self
     */
    public function diff() {
        $args = func_get_args();
        array_unshift($args, $this->items);
        $this->items = call_user_func_array('array_diff', $args);
        return $this;
    }


    /**
     * intersect
     * @see  php::array_intersect
     * 
     * @return self
     */
    public function intersect() {
        $args = func_get_args();
        array_unshift($args, $this->items);
        $this->items = call_user_func_array('array_intersect', $args);
        return $this;
    }


    /**
     * push to $items
     * 
     * @param  mixed $item
     * @return self
     */
    public function push($item) {
        $this->items[] = $item;
        return $this;
    }


    /**
     * shift $items
     * @see  php::array_shift
     * 
     * @return mixed
     */
    public function shift() {
        return array_shift($this->items);
    }


    /**
     * pop $items
     * @see  php::array_pop
     * 
     * @return mixed
     */
    public function pop(){
        return array_pop($this->items);
    }


    /**
     * unshift
     * @see php::array_unshift
     * 
     * @param  [type] $item [description]
     * @return [type]       [description]
     */
    public function unshift($item) {
        array_unshift($this->items, $item);
        return $this;
    }


    /**
     * shuffle $items
     * @see  php::suffle
     * 
     * @return self
     */
    public function shuffle() {
        shuffle($this->items);
        return $this;
    }


    /**
     * return array iterator for $items
     * 
     * @return ArrayIterator
     */
    public function getIterator() {
        return new \ArrayIterator($this->items);
    }


    /**
     * nubmer of $items
     * 
     * @return integer
     */
    public function count() {
        return count($this->items);
    }


    /**
     * return json string of $items
     * 
     * @return string
     */
    public function jsonSerialize() {
        return json_encode($this->items);
    }


    /**
     * set value for offset
     * 
     * @param  string|null $offset
     * @param  mixed $value 
     * 
     * @return self
     */
    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->items[] = $value;
        }
        else {
            $this->items[$offset] = $value;
        }
        return $this;
    }


    /**
     * does offset exist
     * 
     * @param  string|integer $offset
     * 
     * @return boolean
     */
    public function offsetExists($offset) {
        return isset($this->items[$offset]);
    }


    /**
     * unset an offset
     * 
     * @param  string|integer $offset
     * 
     * @return self
     */
    public function offsetUnset($offset) {
        unset($this->items[$offset]);
        return $this;
    }


    /**
     * get an offset
     * 
     * @param  string|integer $offset
     * 
     * @return mixed
     */
    public function offsetGet($offset) {
        return isset($this->items[$offset]) ? $this->items[$offset] : null;
    }


    /**
     * return default array
     * 
     * @return array
     */
    public function toArray() {
        return $this->items;
    }

}
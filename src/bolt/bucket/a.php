<?php

namespace bolt\bucket;
use \b;

class a implements \ArrayAccess, \Iterator, \Countable, \JsonSerializable {

    private $_value = [];

    public function __construct($value) {
        $this->set($value);
    }

    public function set($key, $value=false) {
        if (is_array($key)) {
            array_walk($key, function($value, $key){
                $this->set($key, $value);
            });
            return $this;
        }
        $ref = false;

        if (stripos($key, '.') !== false) {
            $parts = explode('.', $key);
            $key = array_shift($parts);
            $ref = b::bucket('create', $value);
            $node = $ref;
            foreach ($parts as $name) {
                $node = $node->get($name);
            }
            $node->set($value);
        }

        $this->_value[$key] = [
            '_' => $value,
            'ref' => $ref
        ];

        return $this;
    }

    public function get($key, $default=false) {
        $resp = $default;

        if (array_key_exists($key, $this->_value) AND $this->_value[$key]['ref']) {
            return $this->_value[$key]['ref'];
        }
        else if (array_key_exists($key, $this->_value)) {
            $resp = $this->_value[$key]['_'];
        }
        else if (stripos($key, '.') !== false) {
            $parts = explode('.', $key);
            $key = array_shift($parts);
            $node = $this->_value[$key]['ref'];
            foreach ($parts as $name) {
                $node = $node->get($name);
            }
            return $node;
        }

        return $this->_value[$key]['ref'] = b::bucket('create', $resp);
    }

    public function value($key, $default=false) {
        return $this->get($key, $default)->normalize();
    }

    public function normalize() {
        $resp = [];

        foreach ($this->_value as $key => $item) {
            $value = $item['_'];

            // if there are children
            if ($item['ref']) {
                $value = $item['ref']->normalize();
            }

            // pop onto resp
            $resp[$key] = $value;

        }

        return $resp;
    }

    public function jsonSerialize() {

    }


    /**
     * @brief set a value at index
     *
     * @param $offset offset value to set
     * @param $value value
     * @return self
     */
    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->_value[] = $value;
        } else {
            $this->_value[$offset] = $value;
        }
        return $this;
    }

    /**
     * @brief check if an offset exists
     *
     * @param $offset offset name
     * @return bool if offset exists
     */
    public function offsetExists($offset) {
        return isset($this->_value[$offset]);
    }

    /**
     * @brief unset an offset
     *
     * @param $offset offset name
     * @return self
     */
    public function offsetUnset($offset) {
        unset($this->_value[$offset]);
        return $this;
    }

    /**
     * @brief get an offset value
     *
     * @param $offset offset name
     * @return value
     */
    public function offsetGet($offset) {
        return isset($this->_value[$offset]) ? $this->get($offset) : null;
    }

    /**
     * @brief rewind array pointer
     *
     * @return self
     */
    function rewind() {
        reset($this->_value);
        $this->_pos = key($this->_value);
        return $this;
    }

    /**
     * @brief current array pointer
     *
     * @return self
     */
    function current() {
        $var = current($this->_value);
        if ($var === false) {return false;}
        $key = key($this->_value);
        return (b::isInterfaceOf($var, '\bolt\iBucket') ? $var : $this->get(key($this->_value)));
    }

    /**
     * @brief array key pointer
     *
     * @return key
     */
    function key() {
        $var = key($this->_value);
        return $var;
    }

    /**
     * @brief advance array pointer
     *
     * @return current value
     */
    function next() {
        $this->_pos = key($this->_value);
        $var = next($this->_value);
        if ($var === false) {return false;}
        return (b::isInterfaceOf($var, '\bolt\iBucket') ? $var : $this->get(key($this->_value)));
    }

    /**
     * @brief is the current array pointer valid
     *
     * @return current value
     */
    function valid() {
        return array_key_exists(key($this->_value), $this->_value);
    }

    /**
     * @brief get count of data
     *
     * @return count
     */
    function count() {
        return count($this->_value);
    }


}
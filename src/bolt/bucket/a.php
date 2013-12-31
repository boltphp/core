<?php

namespace bolt\bucket;
use \b;


class a implements face, \ArrayAccess, \Iterator, \Countable {

    private $_access = false;

    private $_value = [];

    public function __construct($value=[]) {
        $this->_access = b::bucket("access");
        $this->_value = $value;
    }

    public function __get($name) {
        if ($name === 'value') {
            return $this->normalize();
        }
    }

    public function get($key) {
        if (!is_numeric($key)) {
            $key = '['.trim(str_replace('.', '][', $key), '[]').']';
        }
        return b::bucket('create', $this->_access->getValue($this->_value, $key));
    }

    public function set($key, $value) {
        if (!is_numeric($key)) {
            $key = '['.trim(str_replace('.', '][', $key), '[]').']';
        }
        $this->_access->setValue($this->_value, $key, $value);
        return $this;
    }

    public function normalize() {
        return $this->_value;
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

// class a implements face, \JsonSerializable {

//     private $_value = [];

//     public function __construct($value=null) {
//         // inial value must be an array
//         if ($value !== null) {
//             if (!is_array($value)) {$value = [$value]; }
//             $this->set($value);
//         }
//     }

//     public function set($key, $value=false) {
//         if (is_array($key)) {
//             array_walk($key, function($value, $key){
//                 $this->set($key, $value);
//             });
//             return $this;
//         }

//         $ref = false;

//         if (stripos($key, '.') !== false) {
//             $parts = explode('.', $key);
//             $key = array_shift($parts);
//             $ref = new a();
//             $ref->set(implode(".", $parts), $value);
//             $value = false;
//         }

//         // if value is a bolt object
//         if (b::isInterfaceOf($value, 'bolt\bucket\face')) {
//             $ref = $value;
//             $value = $ref->normalize();
//         }

//         $this->_value[$key] = [
//             '_' => $value,
//             'ref' => $ref
//         ];

//         return $this;
//     }

//     public function get($key, $default=false) {
//         $resp = $default;

//         if (array_key_exists($key, $this->_value) AND $this->_value[$key]['ref']) {
//             return $this->_value[$key]['ref'];
//         }
//         else if (array_key_exists($key, $this->_value)) {
//             $resp = $this->_value[$key]['_'];
//         }
//         else if (stripos($key, '.') !== false) {
//             $parts = explode('.', $key);
//             $key = array_shift($parts);

//             if (!$this->_value[$key]['ref']) {
//                 $this->_value[$key]['ref'] = b::bucket('create', $this->_value[$key]['_']);
//             }

//             return $this->_value[$key]['ref']->get(implode(".", $parts), $default);
//         }

//         return b::bucket('create', $resp);
//     }

//     public function value($key, $default=false) {
//         return $this->get($key, $default)->normalize();
//     }

//     public function normalize() {
//         $resp = [];

//         foreach ($this->_value as $key => $item) {
//             $value = $item['_'];

//             // if there are children
//             if ($item['ref']) {
//                 $value = $item['ref']->normalize();
//             }

//             // pop onto resp
//             $resp[$key] = $value;

//         }

//         return $resp;
//     }

//     public function asArray() {
//         return $this->normalize();
//     }

//     public function jsonSerialize() {

//     }

//     public function exists($key) {
//         if (array_key_exists($key, $this->_value)) {return true;}
//         return $this->value($key, false) !== false;
//     }


//     /**
//      * @brief set a value at index
//      *
//      * @param $offset offset value to set
//      * @param $value value
//      * @return self
//      */
//     public function offsetSet($offset, $value) {
//         if (is_null($offset)) {
//             $this->_value[] = $value;
//         } else {
//             $this->_value[$offset] = $value;
//         }
//         return $this;
//     }

//     /**
//      * @brief check if an offset exists
//      *
//      * @param $offset offset name
//      * @return bool if offset exists
//      */
//     public function offsetExists($offset) {
//         return isset($this->_value[$offset]);
//     }

//     /**
//      * @brief unset an offset
//      *
//      * @param $offset offset name
//      * @return self
//      */
//     public function offsetUnset($offset) {
//         unset($this->_value[$offset]);
//         return $this;
//     }

//     /**
//      * @brief get an offset value
//      *
//      * @param $offset offset name
//      * @return value
//      */
//     public function offsetGet($offset) {
//         return isset($this->_value[$offset]) ? $this->get($offset) : null;
//     }

//     /**
//      * @brief rewind array pointer
//      *
//      * @return self
//      */
//     function rewind() {
//         reset($this->_value);
//         $this->_pos = key($this->_value);
//         return $this;
//     }

//     /**
//      * @brief current array pointer
//      *
//      * @return self
//      */
//     function current() {
//         $var = current($this->_value);
//         if ($var === false) {return false;}
//         $key = key($this->_value);
//         return (b::isInterfaceOf($var, '\bolt\iBucket') ? $var : $this->get(key($this->_value)));
//     }

//     /**
//      * @brief array key pointer
//      *
//      * @return key
//      */
//     function key() {
//         $var = key($this->_value);
//         return $var;
//     }

//     /**
//      * @brief advance array pointer
//      *
//      * @return current value
//      */
//     function next() {
//         $this->_pos = key($this->_value);
//         $var = next($this->_value);
//         if ($var === false) {return false;}
//         return (b::isInterfaceOf($var, '\bolt\iBucket') ? $var : $this->get(key($this->_value)));
//     }

//     /**
//      * @brief is the current array pointer valid
//      *
//      * @return current value
//      */
//     function valid() {
//         return array_key_exists(key($this->_value), $this->_value);
//     }

//     /**
//      * @brief get count of data
//      *
//      * @return count
//      */
//     function count() {
//         return count($this->_value);
//     }


// }
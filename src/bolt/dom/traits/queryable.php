<?php

namespace bolt\dom\traits;

trait queryable {

    /**
     * find a selector
     * @param  string $selector
     *
     * @return boolean
     */
    public function offsetExists($selector) {
        return count($this->find($selector)) > 0;
    }


    /**
     * get a offset
     *
     * @see  self::find
     * @param  string $selector
     *
     * @return bolt\dom\collection
     */
    public function offsetGet($selector) {
        return $this->find($selector);
    }


    /**
     * set the contents of an element
     *
     * @see  self::html
     * @param  string $selector
     * @param  mixed $value
     *
     * @return self
     */
    public function offsetSet($selector, $value) {
        $el = $this->find($selector)->first();

        if (!$el) {return false;}

        $el->html($value);

        return $this;
    }


    /**
     * remove a node if it exists
     *
     * @param  strong $selector
     *
     * @return mixed
     */
    public function offsetUnset($selector) {
        return $this->find($selector)->remove();
    }

}
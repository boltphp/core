<?php

namespace bolt\helpers\fs;
use \b;

use \GlobIterator,
    \FilesystemIterator;

class glob implements \IteratorAggregate, \Countable {

    private $_glob = false;

    private $_path = false;

    private $_flags = false;

    private $_iterator;

    public function __construct($path, $flags = FilesystemIterator::KEY_AS_PATHNAME) {
        $this->_path = $path;
        $this->_flags = $flags;
    }

    public function setPath($path) {
        $this->_path = $path;
        $this->_iterator = false;
        return $this;
    }

    public function getPath($path) {
        return $this->_path;
    }

    public function setFlags($flags) {
        $this->_flags = $flags;
        $this->_iterator = false;
        return $this;
    }

    public function getIterator() {
        if (!$this->_iterator){
            $this->_iterator = new GlobIterator($this->_path, $this->_flags);
        }
        return $this->_iterator;
    }

    public function asArray() {
        return array_keys(iterator_to_array($this->getIterator()));
    }

    public function count() {
        return call_user_func_array([$this->getIterator(), 'count'], func_get_args());
    }

}
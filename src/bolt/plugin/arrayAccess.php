<?php

namespace bolt\plugin;

trait arrayAccess {

    public function offsetSet($name, $class) {
        $this->plug($name, $class);
    }

    public function offsetExists($name) {
        return $this->pluginCanCall($name);
    }

    public function offsetUnset($name) {
        unset($this->_plugins[$name]);
    }
    public function offsetGet($name) {
        return $this->call($name);
    }

}
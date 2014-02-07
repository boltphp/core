<?php

namespace bolt;
use \b;

trait events {

    private $_events = [];

    public function addListener($name, $listener, $args = []) {
        if (!array_key_exists($name, $this->_events)) {
            $this->_events[$name] = [];
        }

        // give back
        return $this->_events[$name][] = new events\listener($this, $listener, $args);
    }

    public function removeListener(bolt\events\listener $listener) {

    }

    public function getListeners($name) {
        return $this->_events[$name];
    }

    public function on() {
        return call_user_func_array([$this, 'addListener'], func_get_args());
    }

    public function off() {
        return call_user_func_array([$this, 'removeListener'], func_get_args());
    }


    protected function fire($name, $data=[]) {
        if (!array_key_exists($name, $this->_events)) { return false; }

        // event
        foreach ($this->_events[$name] as $listener) {
            $listener->execute(new events\event($this, $listener, $data));
        }

        return $this;
    }




}

<?php

namespace bolt;
use \b;

/**
 * Add events handling to an object
 */
trait events {

    /**
     * @var bolt\events\listener[$type][]
     */
    private $_events = [];


    /**
     * add a listener
     *
     * @param string $type type of listener
     * @param Closure $cb closure functin to execute
     * @param array $args arguments to pass back to event
     *
     * @return bolt\events\listener
     */
    public function addListener($type, $cb, $args = []) {
        if (!array_key_exists($type, $this->_events)) {
            $this->_events[$type] = [];
        }
        return $this->_events[$type][] = new events\listener($this, $cb, $type, $args);
    }


    /**
     * remove a listener from the object
     *
     * @param bolt\events\listener $listener listener to remove
     *
     * @return self
     */
    public function removeListener(events\listener $listener) {
        foreach ($this->_events[$listener->type] as $i => $obj) {
            if ($obj->guid === $listener->guid) {
                unset($this->_events[$listener->type][$i]);
            }
        }
        return $this;
    }


    /**
     * return all listeners for a given type
     *
     * @param string $type type of listener
     *
     * @return array
     */
    public function getListeners($type) {
        return array_key_exists($type, $this->_events) ? $this->_events[$type] : [];
    }


    /**
     * return all listeners, grouped by type
     *
     * @return array
     */
    public function getAllListeners() {
        return $this->_events;
    }

    /**
     * attach a handler that runs only once
     *
     * @see addListener
     *
     * @return bolt\events\listener
     */
    public function once() {
        $l = call_user_func_array([$this, 'addListener'], func_get_args());
        $l->once(true);
        return $l;
    }

    /**
     * attach a listener
     *
     * @see addListener
     */
    public function on() {
        return call_user_func_array([$this, 'addListener'], func_get_args());
    }


    /**
     * remove a listener
     *
     * @see removeListener
     */
    public function off() {
        return call_user_func_array([$this, 'removeListener'], func_get_args());
    }


    /**
     * fire an event type
     *
     * @param string $type type of listener to fire
     * @param array $data data to send along
     *
     *
     */
    public function fire($type, $data=[]) {
        if (!array_key_exists($type, $this->_events)) { return false; }

        // event
        foreach ($this->_events[$type] as $listener) {
            $listener->execute(new events\event($listener, $data));
        }

        return $this;
    }




}

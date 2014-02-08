<?php

namespace bolt\events;
use \b;


/**
 * Event Object
 */
class event {

    /**
     * @var array
     */
    private $_data = [];

    /**
     * @var bolt\events\listener
     */
    private $_listener;


    /**
     * Construct
     *
     * @param bolt\events\listener $listener the execute listener
     * @param array $data data for event
     */
    public function __construct(\bolt\events\listener $listener, $data = []) {
        $this->_listener = $listener;
        $this->_data = $data;
    }

    /**
     * return private variables
     *
     * @return mixed
     */
    public function __get($name) {
        switch($name) {
            case 'parent': return $this->_listener->parent;
            case 'listener': return $this->_listener;
            case 'type': return $this->_listener->type;
            case 'data': return $this->_data;
            case 'args': return $this->_listener->args;
        };
        if (array_key_exists($name, $this->_data)) {
            return $this->_data[$name];
        }
        return null;
    }

    /**
     * return a data value or default
     *
     * @param string $name name of data
     * @param mixed $default default value
     *
     * @return mixed
     */
    public function data($name, $default=null) {
        return array_key_exists($name, $this->_data) ? $this->_data[$name] : $default;
    }

    /**
     * return a arg value or default
     *
     * @param string $name name of arg key
     * @param mixed $default default value
     *
     * @return mixed
     */
    public function arg($name, $default=null) {
        return array_key_exists($name, $this->_listener->args) ? $this->_listener->args[$name] : $default;
    }

}
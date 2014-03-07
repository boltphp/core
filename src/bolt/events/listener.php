<?php

namespace bolt\events;
use \b;

use \Closure as cc;

/**
 * event listener
 */
class listener {

    /**
     * @var string
     */
    private $_guid;

    /**
     * @var object
     */
    private $_parent;

    /**
     * @var mixed
     */
    private $_callback;

    /**
     * @var array
     */
    private $_args;

    /**
     * @var string
     */
    private $_type;

    /**
     * @var object
     */
    private $_context;

    /**
     * @var bool
     */
    private $_once = false;


    /**
     * Construct
     *
     * @param object $parent parent object. must use bolt\events
     * @param mixed $callback what to execute on fire
     * @param array $args arguments to pass
     * @param object $context context to execute closure event in
     *
     */
    public function __construct($parent, $callback, $type, $args = [], $context=false) {


        if (!in_array('bolt\events', b::classUses($parent))) {
            throw new \Exception('Parent must use bolt\events');
        }
        $this->_guid = b::guid('event');
        $this->_parent = $parent;
        $this->_callback = $callback;
        $this->_type = $type;
        $this->_args = $args;


        is_object($context) ? $this->context($context) : $this->context($parent);

    }

    /**
     * return private variables
     *
     * @param string $name name of variable
     *
     * @return mixed
     */
    public function __get($name) {
        switch($name) {
            case 'args': return $this->_args;
            case 'type': return $this->_type;
            case 'guid': return $this->_guid;
            case 'callback': return $this->_callback;
            case 'parent': return $this->_parent;
            case 'context': return $this->_context;
            case 'once': return $this->_once;
        };
        return null;
    }


    /**
     * set once variable
     *
     * @param bool $once run the listener once
     *
     * @return self
     */
    public function once($once) {
        $this->_once = $once;
        return $this;
    }


    /**
     * context to execute callback in
     *
     * @param object $context object context for closure
     *
     * @return self
     */
    public function context($context) {
        if (is_a($this->_callback, 'Closure')) {
            $this->_context = $context;
            $this->_callback = cc::bind($this->_callback, $this->_context);
        }
        return $this;
    }


    /**
     * detach listener from parent
     *
     * @return void
     */
    public function detach() {
        $this->_parent->off($this);
    }


    /**
     * execute the event callback
     *
     * @param bolt\events\event $e
     *
     * @return self
     */
    public function execute(event $e) {
        call_user_func($this->_callback, $e);
        if ($this->_once) {
            $this->detach();
        }
        return $this;
    }

}
<?php

namespace bolt\models;
use \b;

/**
 * model proxy
 */
class proxy {

    /**
     * models manager
     *
     * @var bolt\models
     */
    private $_manager;

    /**
     * entity class name
     *
     * @var string
     */
    private $_class;



    /**
     * Constructor
     *
     * @param bolt\models $manager
     * @param string $class
     */
    public function __construct(\bolt\models $manager, $class) {
        $this->_manager = $manager;
        $this->_class = $class;
    }

    /**
     * return the class name
     *
     * @return string
     */
    public function getClassName() {
        return $this->_class;
    }



    /**
     * proxy function class to manager
     *
     * @param string $name function name
     * @param array $args
     *
     * @return mixed
     */
    public function __call($name, $args) {
        if (method_exists($this->_manager, $name)) {
            array_unshift($args, $this->_class);
            return call_user_func_array([$this->_manager, $name], $args);
        }
        return null;
    }

}
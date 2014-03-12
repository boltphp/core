<?php

namespace bolt\models;


/**
 * model collection
 */
class collection extends \bolt\helpers\collection {

    /**
     * model manager
     *
     * @var bolt\models
     */
    private $_manager;

    /**
     * entity class name
     *
     * @var string
     */
    private $_entity;


    /**
     * paramaters that can be defined on collection
     *
     * @var array
     */
    private $_paramaters = [];


    /**
     * Constructor
     *
     * @param bolt\models $manager
     * @param string $entity entity manager
     * @param array $items inital items
     */
    public function __construct(\bolt\models $manager, $entity, $items = []) {
        $this->_manager = $manager;
        $this->_entity = $entity;

        if (!class_exists($entity, true)) {
            throw new \Exception("Entity class $entity does not exist");
        }

        if (isset($items)) {
            foreach ($items as $item) {
                $this->push($item);
            }
        }

    }

    /**
     * get a paramter
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name) {
        if (array_key_exists($name, $this->_paramaters)) {
            return $this->_paramaters[$name];
        }
        return null;
    }


    /**
     * set a paramater
     * @param string $name
     * @param mixed $value
     *
     * @return self
     */
    public function __set($name, $value) {
        $this->_paramaters[$name] = $value;
        return $this;
    }


    /**
     * push an object to the stack
     *
     * @param object $object
     *
     * @return self
     */
    public function push($object) {
        if (!is_a($object, $this->_entity)) {
            throw new \Exception("trying to attach a non-enitity object");
        }
        $object->setManager($this->_manager);
        parent::push($object);
        return $this;
    }


    /**
     * unshift an object
     *
     * @param object $object
     *
     * @return self
     */
    public function unshift($object) {
        if (!is_a($object, $this->_entity)) {
            throw new \Exception("trying to attach a non-enitity object");
        }
        $object->setManager($this->_manager);
        parent::unshift($object);
        return $this;
    }


}
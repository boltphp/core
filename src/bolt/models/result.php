<?php

namespace bolt\models;


/**
 * model results
 */
class result extends \bolt\helpers\collection {

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
     * Constructor
     *
     * @param bolt\models $manager
     * @param string $entity entity manager
     * @param array $items inital items
     */
    public function __construct(\bolt\models $manager, $entity, $items = []) {
        $this->_manager = $manager;
        $this->_entity = $entity;


        if (isset($items)) {
            foreach ($items as $item) {
                $this->push($item);
            }
        }

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
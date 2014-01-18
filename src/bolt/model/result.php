<?php

namespace bolt\model;

class result  {

    private $_items;
    private $_entity;
    private $_manager;

    public function __construct($entity, $manager) {
        $this->_entity = $entity;
        $this->_manager = $manager;
    }

    public function setItems($items) {
        $this->_value = $items;
    }

}
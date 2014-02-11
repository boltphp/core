<?php

namespace bolt\models;

use \SplDoublyLinkedList;

class result extends SplDoublyLinkedList {

    private $_manager;

    private $_entity;

    public function __construct(\bolt\models $manager, $entity, $items = []) {
        $this->_manager = $manager;
        $this->_entity = $entity;

        // FIFO
        $this->setIteratorMode(SplDoublyLinkedList::IT_MODE_FIFO);

        if (isset($items)) {
            foreach ($items as $item) {
                $this->push($item);
            }
        }

    }

    public function push($object) {
        if (!is_a($object, 'bolt\models\entity')) {
            throw new \Exception("trying to attach a non-enitity object");
            return false;
        }

        $object->setManager($this->_manager);
        $object->setLoaded(true);

        return parent::push($object);
    }


    public function unshift($object) {
        if (!is_a($object, 'bolt\models\entity')) {
            throw new \Exception("trying to attach a non-enitity object");
            return false;
        }

        $object->setManager($this->_manager);
        $object->setLoaded(true);

        return parent::unshift($object);
    }


    public function first() {
        return count($this) > 0 ? $this[0] : null;
    }

    public function last() {
        return count($this) > 0 ? $this[count($this) - 1] : null;
    }

}
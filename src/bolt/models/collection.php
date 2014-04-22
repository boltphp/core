<?php

namespace bolt\models;
use \b;

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


    private $_total = null;

    private $_offset = null;

    private $_limit = null;

    /**
     * Constructor
     *
     * @param bolt\models $manager
     * @param string $entity entity manager
     * @param array $items inital items
     * @param array $opts pagination optiosn
     */
    public function __construct(\bolt\models $manager, $entity, $items = [], $opts = []) {
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

        $this->_total = b::param('total', null, $opts);
        $this->_offset = b::param('offset', null, $opts);
        $this->_limit = b::param('limit', null, $opts);

        $this->_paramaters = $opts;

    }

    public function setTotal($total) {
        $this->_total = $total;
        return $this;
    }

    public function getTotal() {
        return $this->_total;
    }

    public function setOffset($offset) {
        $this->_offset = $offset;
        return $this;
    }

    public function getOffset() {
        return $this->_offset;
    }

    public function setLimit($limit) {
        return $this->_limit;
    }

    public function getLimit() {
        return $this->_limit;
    }

    public function getCurrentPage() {
        if ($this->_offset === null || $this->_limit === null) {return null;}
        return ($this->_limit === 0 || $this->_offset === 0) ? 1 : floor($this->_offset / $this->_limit) + 1;
    }

    public function getNumPages() {
        if ($this->_total === null || $this->_limit === null) {return null;}
        return ceil($this->_total / $this->_limit);
    }

    public function getLastPage() {
        return $this->getNumPages();
    }

    public function getFirstPage() {
        return 1;
    }

    public function hasPage($page) {
        $num = $this->getNumPages();
        return $num === null ? null : ($page <= $num && $num > 0);
    }

    public function hasPages() {
        $pg = $this->getNumPages();
        return $pg == null ? false : ($this->getNumPages() > 0);
    }

    public function hasNextPage() {
        $pg = $this->getCurrentPage();
        return $pg === null ? null : $this->hasPage($pg + 1);
    }

    public function hasPrevPage() {
        $pg = $this->getCurrentPage();
        return $pg === null ? null : $this->hasPage($pg - 1);
    }

    public function getPageRange() {
        $pages = $this->getNumPages();
        return $pages === null ? null : range(1, $pages);
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


    public function asArray() {
        $resp = [];
        foreach ($this as $item) {
            $resp[] = is_array($item) ? $item : $item->asArray();
        }
        return $resp;
    }

}
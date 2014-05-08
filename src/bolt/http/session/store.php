<?php

namespace bolt\http\session;

use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;

use Symfony\Component\HttpFoundation\Session\Storage\MetadataBag,
    Symfony\Component\HttpFoundation\Session\Storage\SessionBagInterface
    ;


class store implements SessionStorageInterface {

    private $_manager;

    private $_driver;

    private $_name;

    private $_started = false;

    private $_closed = true;

    private $_id;

    private $_bags = [];

    private $_metadataBag;


    public function __construct(\bolt\http\session $manager, $name, \SessionHandlerInterface $driver) {
        $this->setName($name);

        $this->_metadataBag = new MetadataBag();

        $this->_manager = $manager;
        $this->_driver = $driver;
    }

    public function start() {
        if ($this->_started || !$this->_closed) {
            return true;
        }

        if ($this->_id && ($data = $this->_driver->read($this->_id)) != null) {
            foreach ($data as $bagName => $values) {
                $this->_bags[$bagName]->initialize($values);
            }
        }

        if (!$this->_id) {
            $this->setId($this->generateId());
        }

        $this->_started = true;
        $this->_closed = false;

        return true;

    }

    public function isStarted() {
        return $this->_started;
    }

    public function getId() {
        return $this->_id;
    }

    public function setId($id) {
        $this->_id = $id;
        return $this;
    }

    public function getName() {
        return $this->_name;
    }

    public function setName($name) {
        $this->_name = $name;
        return $this;
    }

    public function regenerate($destroy = false, $lifetime = null) {
        if ($destroy) {
            $this->_driver->destroy($this->_id);
        }
        $this->_id = $this->generateId();
        $this->started = true;
        $this->closed = false;
    }

    public function generateId() {
        return hash('sha256', uniqid(mt_rand()));
    }

    public function save() {
        $data = [];
        foreach ($this->_bags as $bag) {
            $data[$bag->getName()] = $bag->all();
        }
        $this->_driver->write($this->_id, $data);
        $this->_closed = true;
        $this->_started = false;
        return $this;
    }

    public function clear() {
        foreach ($this->_bags as $bag) {
            $bag->clear();
        }
        return $this;
    }

    public function getBag($name) {
        if (!isset($this->_bags[$name])) {
            throw new \Exception("Bag with name $name does not exist.");
        }
        return $this->_bags[$name];
    }

    public function registerBag(\Symfony\Component\HttpFoundation\Session\SessionBagInterface $bag) {
        $this->_bags[$bag->getName()] = $bag;
        return $this;
    }

    public function setMetadataBag(MetadataBag $bag) {
        if ($bag === null) {
            $bag = new MetadataBag();
        }
        $this->_metadataBag = $bat;
        return $this;
    }

    public function getMetadataBag() {
        return $this->_metadataBag;
    }

};
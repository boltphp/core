<?php

namespace bolt\source;

use \Doctrine\DBAL\Configuration;
use \Doctrine\DBAL\DriverManager;

class db {

    private $_config = [];
    private $_handle = false;

    public function __construct($config) {
        $this->_config = $config;
    }

    public function query() {
        $qb = $this->getHandle();
        return $qb->createQueryBuilder();
    }

    public function getHandle() {
        if (!$this->_handle) {
            $this->_handle = DriverManager::getConnection($this->_config, new Configuration());
        }
        return $this->_handle;
    }

    public function create() {

    }

    public function read() {

    }

    public function update() {

    }

    public function delete() {


    }

}
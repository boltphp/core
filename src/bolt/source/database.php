<?php

namespace bolt\source;
use \b;

use \Doctrine\DBAL\Configuration,
    \Doctrine\DBAL\DriverManager;

class database {

    private $_app;

    private $_config;

    private $_handle;

    public function __construct(\bolt\application $app, $config = []) {
        $this->_app = $app;
        $this->_config = $config;
    }

    public function getHandle() {
        if (!$this->_handle) {
            $this->_handle = DriverManager::getConnection($this->_config, new Configuration());
        }
        return $this->_handle;
    }

    public function getModelEntityManager(\bolt\models $manager, \bolt\models\driver $driver) {

        // configure
        $cfg = new \Doctrine\ORM\Configuration();

        // set
        $cfg->setMetadataDriverImpl($driver);

        $cfg->setProxyDir("/tmp");
        $cfg->setProxyNamespace('bolt\models\proxy');

        // create our entity manager
        return \Doctrine\ORM\EntityManager::create($this->getHandle(), $cfg, $this->getHandle()->getEventManager());

    }

}
<?php

namespace bolt\source;
use \b;

use \Doctrine\DBAL\Configuration,
    \Doctrine\DBAL\DriverManager;

class database implements face {

    private $_app;

    private $_config;

    private static $_handles = [];

    public function __construct(\bolt\application $app, $config = []) {
        $this->_app = $app;
        $this->_config = $config;
    }

    public function getHandle() {
        $cid = md5(serialize(array_filter($this->_config, function($name) { return in_array($name, ['driver','dbname','host','username','password']); })));
        if (!array_key_exists($cid, self::$_handles)) {
            self::$_handles[$cid] = DriverManager::getConnection($this->_config, new Configuration());
        }
        return self::$_handles[$cid];
    }

    public function getConfig() {
        return $this->_config;
    }

    public function getModelEntityManager(\bolt\models $manager, \bolt\models\driver $driver) {

        // configure
        $cfg = new \Doctrine\ORM\Configuration();

        // set
        $cfg->setMetadataDriverImpl($driver);

        $cfg->setProxyDir("/tmp");
        $cfg->setProxyNamespace('bolt\models\proxy');


        if (isset($this->_config['queryCache'])) {
            $cfg->setQueryCacheImpl($this->_config['queryCache']);
        }

        if (isset($this->_config['metadataCache'])) {
            $cfg->setMetadataCacheImpl($this->_config['metadataCache']);
        }

        if (isset($this->_config['resultsCache'])) {
            $cfg->setResultCacheImpl($this->_config['resultsCache']);
        }

        $cfg->addCustomStringFunction('FIND_IN_SET', '\bolt\models\function\findInSet');
        $cfg->addCustomStringFunction('FROM_UNIXTIME', '\bolt\models\function\fromUnixTime');

        // handle
        $this->getHandle()->getEventManager()->addEventSubscriber(new event($manager));

        // create our entity manager
        return \Doctrine\ORM\EntityManager::create($this->getHandle(), $cfg, $this->getHandle()->getEventManager());

    }

    public function __call($name, $args) {
        if (method_exists($this->getHandle(), $name)) {
            return call_user_func_array([$this->getHandle(), $name], $args);
        }
    }

}

class event implements \Doctrine\Common\EventSubscriber {

    private $_manager;

    public function __construct(\bolt\models $manager) {
        $this->_manager = $manager;
    }

    public function postLoad($e) {
        $e->getEntity()
            ->setLoaded(true)
            ->setManager($this->_manager);
    }

    public function getSubscribedEvents() {
        return ['postLoad'];
    }
}
<?php

namespace bolt\source;
use \b;

use \Doctrine\DBAL\Configuration,
    \Doctrine\DBAL\DriverManager;


/**
 * database source
 */
class database implements sourceInterface {

    /**
     * application
     * 
     * @var bolt\application
     */
    private $_app;

    /**
     * base configuration
     * 
     * @var array
     */
    private $_config;

    /**
     * database handles
     * 
     * @var array
     */
    private static $_handles = [];

    /**
     * Constructor
     * @param bolt\application $app
     * @param array $config
     *  
     */
    public function __construct(\bolt\application $app, array $config = []) {
        $this->_app = $app;
        $this->_config = $config;
    }


    /**
     * return the DABL handle
     * 
     * @return Doctrine\DBAL\DriverManager
     */
    public function getHandle() {
        $cid = md5(serialize(array_filter($this->_config, function($name) { return in_array($name, ['driver','dbname','host','user','password']); })));
        if (!array_key_exists($cid, self::$_handles)) {
            self::$_handles[$cid] = DriverManager::getConnection($this->_config, new Configuration());
        }
        return self::$_handles[$cid];
    }


    /**
     * return the configuration
     * 
     * @return array
     */
    public function getConfig() {
        return $this->_config;
    }


    /**
     * get the doctrine entity manager
     * 
     * @param  bolt\models $manager
     * @param  bolt\models\driver $driver
     * 
     * @return Doctrine\ORM\EntityManager
     */
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

        $cfg->addCustomStringFunction('FIND_IN_SET', '\bolt\models\functions\findInSet');
        $cfg->addCustomStringFunction('FROM_UNIXTIME', '\bolt\models\functions\fromUnixTime');

        // handle
        $this->getHandle()->getEventManager()->addEventSubscriber(new event($manager));

        // create our entity manager
        return \Doctrine\ORM\EntityManager::create($this->getHandle(), $cfg, $this->getHandle()->getEventManager());

    }

    /**
     * magic method to pass any calls 
     * to DABL handler
     * 
     * @param  string $name
     * @param  array $args
     * 
     * @return mixed
     */
    public function __call($name, $args) {
        if (method_exists($this->getHandle(), $name)) {
            return call_user_func_array([$this->getHandle(), $name], $args);
        }
        return null;
    }

}


/**
 * event subscriber
 */
class event implements \Doctrine\Common\EventSubscriber {

    /**
     * models manager
     * 
     * @var bolt\models
     */
    private $_manager;

    /**
     * Constructor
     * @param boltmodels $manager
     */
    public function __construct(\bolt\models $manager) {
        $this->_manager = $manager;
    }


    /**
     * postLoad event callback
     * 
     * @param  Doctrine\Common\Event $e
     * 
     * @return void
     */
    public function postLoad($e) {
        $e->getEntity()
            ->setLoaded(true)
            ->setManager($this->_manager);
    }


    /**
     * list of events this subscriber can handle
     * 
     * @return array
     */
    public function getSubscribedEvents() {
        return ['postLoad'];
    }
}
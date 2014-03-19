<?php

namespace bolt\source;
use \b;

use Doctrine\MongoDB\Connection,
    Doctrine\ODM\MongoDB\Configuration,
    Doctrine\ODM\MongoDB\DocumentManager;

class mongodb implements face {

    private $_app;

    private $_config;

    private $_handle;

    public function __construct(\bolt\application $app, $config = []) {
        $this->_app = $app;
        $this->_config = $config;
    }

    public function getHandle() {
        if (!$this->_handle) {

            //
            $server = b::param('server', null, $this->_config);
            $opts = b::param('options', [], $this->_config);

            // handle
            $this->_handle = new Connection($server, $opts);

        }
        return $this->_handle;
    }

    public function getModelEntityManager(\bolt\models $manager, \bolt\models\driver $driver) {

        // configure
        $cfg = new Configuration();

        // set
        $cfg->setMetadataDriverImpl($driver);

        $cfg->setProxyDir("/tmp");
        $cfg->setProxyNamespace('bolt\models\proxy');
        $cfg->setHydratorDir('/tmp');
        $cfg->setHydratorNamespace('bolt\models\hydrators');

        // create our entity manager
        return DocumentManager::create($this->getHandle(), $cfg, $this->getHandle()->getEventManager());

    }

}
<?php

namespace bolt\source;
use \b;

use \Guzzle\Http\Client;


/**
 * curl manager
 */
class curl implements sourceInterface {
    use \bolt\helpers\loggable;

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
     * guzzle http client
     * 
     * @var Guzzle\Http\Client
     */
    private $_client;

    /**
     * model manager
     * 
     * @var bolt\models
     */
    private $_modelManager;

    /**
     * models storage driver
     * 
     * @var bolt\models\driver
     */
    private $_modelDriver;

    /**
     * entity repositories
     * 
     * @var array
     */
    private $_repositories = [];


    /**
     * Constructor
     * 
     * @param bolt\application $app
     * @param array $config
     */
    public function __construct(\bolt\application $app, array $config = []) {
        $this->_app = $app;
        $this->_config = $config;

        $this->_client = new Client(null, ['ssl.certificate_authority' => false]);

        if (isset($config['baseUrl'])) {
            $this->_client->setBaseUrl($config['baseUrl']);
        }

    }

    public function getApp() {
        return $this->_app;
    }


    /**
     * return guzzle client
     * 
     * @return Guzzle\Http\Client
     */
    public function getClient() {
        return $this->_client;
    }


    /**
     * return the entity manager
     * 
     * @param  bolt\models $manager
     * @param  bolt\models\driver $driver
     * 
     * @return self
     */
    public function getModelEntityManager(\bolt\models $manager, \bolt\models\driver $driver) {
        $this->_modelManager = $manager;
        $this->_modelDriver = $driver;
        return $this;
    }


    /**
     * get a repository for a given entity class
     * 
     * @param  string $entity
     * 
     * @return bolt\curl\repository
     */
    public function getRepository($entity) {
        if (array_key_exists($entity, $this->_repositories)) {
            return $this->_repositories[$entity];
        }
        return $this->_repositories[$entity] = new curl\repository($this, $entity, $this->_modelManager, $this->_modelDriver);
    }


    /**
     * magic call to send any undefined
     * methods to $_client
     * 
     * @param  string $name
     * @param  array $args
     * 
     * @return mixed
     */
    public function __call($name, $args) {

        if (method_exists($this->_client, $name)) {
            return call_user_func_array([$this->_client, $name], $args);
        }
        return null;
    }


}
<?php

namespace bolt\source;
use \b;

use \Guzzle\Http\Client;


class curl {

    private $_app;

    private $_config;

    private $_client;

    private $_modelManager;
    private $_modelDriver;
    private $_repositories = [];

    public function __construct(\bolt\application $app, $config = []) {
        $this->_app = $app;
        $this->_config = $config;

        $this->_client = new Client();

        if (isset($config['baseUrl'])) {
            $this->_client->setBaseUrl($config['baseUrl']);
        }

    }

    public function getModelEntityManager(\bolt\models $manager, \bolt\models\driver $driver) {
        $this->_modelManager = $manager;
        $this->_modelDriver = $driver;
        return $this;
    }

    public function getRepository($entity) {
        if (array_key_exists($entity, $this->_repositories)) {
            return $this->_repositories[$entity];
        }
        return $this->_repositories[$entity] = new curl\repository($this, $entity, $this->_modelManager, $this->_modelDriver);
    }

    public function __call($name, $args) {

        if (method_exists($this->_client, $name)) {
            return call_user_func_array([$this->_client, $name], $args);
        }


    }


}
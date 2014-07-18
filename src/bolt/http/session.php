<?php

namespace bolt\http;
use \b;

use Symfony\Component\HttpFoundation\Session\Session as SymfonySession,
    Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage,
    Symfony\Component\HttpFoundation\Session\Storage\Handler\MemcachedSessionHandler
    ;

use Symfony\Component\HttpFoundation\Cookie;


class session implements \bolt\plugin\singleton, \ArrayAccess {

    private $_http;

    private $_config;

    private $_session;

    private $_name;

    private $_handler;

    private $_store;

    public function __construct(\bolt\http $http, array $config = []) {
        $driver = null;

        $this->_http = $http;

        if (!isset($config['handler'])) {
            throw new \Exception("No storage handler provided");
        }
        if (!is_a($config['handler'], 'SessionHandlerInterface')){
            throw new \Exception("Storage handler must implement 'SessionHandlerInterface'.");
        }

        $this->_handler = $config['handler'];
        $this->_handler->setManager($this);

        $this->_name = b::param('name', 'b', $config);


        $this->_store = new session\store($this, $this->_name, $this->_handler);
        $this->_session = new SymfonySession($this->_store);


    }

    public function getHttp() {
        return $this->_http;
    }

    public function getName() {
        return $this->_name;
    }

    public function start() {
        $this->_session->start();
        return $this;
    }

    public function destroy() {
        $this->_session->clear();
        $this->_store->destroy();
        $this->_http->response->headers->clearCookie($this->_name);
        return $this;
    }

    public function set($name, $value) {
        if (!$this->isStarted()) {
            $this->start();
        }
        $this->_session->set($name, $value);
        return $this;
    }

    public function __get($name) {
        return $this->get($name);
    }

    public function __set($name, $value) {
        return $this->set($name, $value);
    }

    public function __isset($name) {
        return $this->has($name);
    }

    public function __call($name, $args) {
        if (method_exists($this->_session, $name)) {
            return call_user_func_array([$this->_session, $name], $args);
        }
        return null;
    }

    public function offsetGet($name) {
        return $this->get($name);
    }

    public function offsetSet($name, $value)  {
        return $this->set($name, $value);
    }

    public function offsetUnset($name) {
        return $this->remove($name);
    }

    public function offsetExists($name) {
        return $this->has($name);
    }

}
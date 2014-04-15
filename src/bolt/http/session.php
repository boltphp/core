<?php

namespace bolt\http;
use \b;

use Symfony\Component\HttpFoundation\Session\Session as SymfonySession,
    Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage,
    Symfony\Component\HttpFoundation\Session\Storage\Handler\MemcachedSessionHandler
    ;

class session implements \bolt\plugin\singleton, \ArrayAccess {

    private $_http;

    private $_config;

    private $_session;

    private $_name;

    private $_driver;

    public function __construct(\bolt\http $http, array $config = []) {
        $driver = null;

        // storage
        $s = b::param('storage', null, $config);

        if ($s) {
            $this->_driver = new MemcachedSessionHandler($s['memcached']);
        }

        $this->_name = b::param('name', 'b', $config);

        // $this->_session = new SymfonySession(new session\store($this, $this->_name, $driver));

    }

    public function getName() {
        return $this->_name;
    }

    public function start() {
        $this->_session->start();
        return $this;
    }

    public function __get($name) {
        return $this->_session->get($name);
    }

    public function __set($name, $value) {
        return $this->_session->set($name, $value);
    }

    public function __isset($name) {
        return $this->_session->has($name);
    }

    public function __call($name, $args) {
        if (method_exists($this->_session, $name)) {
            return call_user_func_array([$this->_session, $name], $args);
        }
        return null;
    }

    public function offsetGet($name) {
        return $this->_session->get($name);
    }

    public function offsetSet($name, $value)  {
        return $this->_session->set($name, $value);
    }

    public function offsetUnset($name) {
        return $this->_session->remove($name);
    }

    public function offsetExists($name) {
        return $this->_session->has($name);
    }

}
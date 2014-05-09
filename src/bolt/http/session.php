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

    private $_driver;

    private $_lifetime = null;

    private $_store;

    public function __construct(\bolt\http $http, array $config = []) {
        $driver = null;

        $this->_http = $http;

        // storage
        $s = b::param('storage', null, $config);

        if ($s) {
            $this->_driver = new MemcachedSessionHandler($s['memcached']);
        }

        $this->_name = b::param('name', 'b', $config);

        $this->_lifetime = b::param('lifetime', null, $config);

        $this->_store = new session\store($this, $this->_name, $this->_driver);
        $this->_session = new SymfonySession($this->_store);


    }

    public function getName() {
        return $this->_name;
    }

    public function setSessionCookie($lifetime = null, $path = '/', $domain = null, $secure = false, $httpOnly = true) {
        $_id = $this->_http->request->cookies->get($this->_name);
        if ($this->getId() === $_id) {return $this;}
        $lifetime = $lifetime ?: $this->_lifetime;
        $c = new Cookie($this->_name, $this->_session->getId(), $lifetime, $path, $domain, $secure, $httpOnly);
        $this->_http->response->headers->setCookie($c);
        return $this;
    }

    public function start() {
        $this->_session->start();
        $this->setSessionCookie();
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
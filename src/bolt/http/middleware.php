<?php

namespace bolt\http;
use \b;

/**
 * Abstract middleware class
 */
abstract class middleware {

    /**
     * @var bolt\http
     */
    protected $http = false;

    /**
     * @var array
     */
    protected $config = false;


    /**
     * Constructor
     *
     * @param bolt\http $http
     * @param array $config array of configs to pass to middleware constructor
     */
    final public function __construct(\bolt\http $http, $config=[]) {
        $this->http = $http;
        $this->config = $config;
        $this->init();
    }

    /**
     * default init class
     *
     * @return void
     */
    public function init() {}


    /**
     * magic get
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name) {
        switch($name) {
            case 'request':
                return $this->http->getRequest();
            case 'response':
                return $this->http->getResponse();
        };
        return null;
    }


    /**
     * execute a middleware method
     *
     * @param string $method name of middleware method
     * @param array $args list of arguments to pass to method
     *
     * @return mixed
     */
    final public function execute($method, $args = []) {

        // get the method ref
        $ref =  b::getReflectionClass($this)->getMethod($method);

        // call it
        return call_user_func_array([$this, $method], $this->getArgsFromMethodRef($ref, $args));

    }


    /**
     * get a list arguments from a method ref
     *
     * @param object $ref
     * @param array $params
     *
     * @return array
     */
    protected function getArgsFromMethodRef($ref, $params) {
        $args = [];


        // must be a subclass of ReflectionFunctionAbstract
        if (!is_subclass_of($ref, 'ReflectionFunctionAbstract')) {
            throw new \Exception('Class must be an implementation of "ReflectionFunctionAbstract"');
        }

        foreach ($ref->getParameters() as $param) {
            $name = $param->getName();

            if ($param->getClass()) {

                if ($param->getClass()->name === 'bolt\http\request') {
                    $args[] = $this->http->request;
                }
                else if ($param->getClass()->name === 'bolt\http\response') {
                    $args[] = $this->http->response;
                }
                else if ($param->getClass()->name === 'bolt\http') {
                    $args[] = $this->http;
                }
                else if ($param->getClass()->name === 'bolt\application') {
                    $args[] = $this->http->app;
                }

            }
            else if ($name == 'request' || $name == 'req') {
                $args[] =  $this->http->request;
            }
            else if ($name == 'response' || $name == 'resp') {
                $args[] =  $this->http->response;
            }
            else if ($name == 'http'){
                $args[] =  $this->http;
            }
            else if ($name == 'app') {
                $args[] =  $this->http->app;
            }
            else if ($name === 'args') {
                $args[] = $params;
            }
            else if (array_key_exists($name, $params)) {
                $args[] = $params[$name];
            }
            else if ($param->isOptional()) {
                $args[] = $param->getDefaultValue();
            }

        }

        return $args;
    }

}
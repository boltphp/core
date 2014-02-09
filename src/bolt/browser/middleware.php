<?php

namespace bolt\browser;
use \b;

use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * Abstract middleware class
 */
abstract class middleware {

    /**
     * @var bolt\browser
     */
    protected $browser = false;

    /**
     * @var array
     */
    protected $config = false;


    /**
     * Constructor
     *
     * @param bolt\browser $browser
     * @param array $config array of configs to pass to middleware constructor
     */
    final public function __construct(\bolt\browser $browser, $config=[]) {
        $this->browser = $browser;
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
                return $this->browser->getRequest();
            case 'response':
                return $this->browser->getResponse();
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
            return false;
        }

        foreach ($ref->getParameters() as $param) {
            $name = $param->getName();

            if ($param->getClass()) {

                if ($param->getClass()->name === 'bolt\browser\request') {
                    $args[] = $this->browser->request;
                }
                else if ($param->getClass()->name === 'bolt\browser\response') {
                    $args[] = $this->browser->response;
                }
                else if ($param->getClass()->name === 'bolt\browser') {
                    $args[] = $this->browser;
                }
                else if ($param->getClass()->name === 'bolt\application') {
                    $args[] = $this->browser->app;
                }

            }
            else if ($name == 'request' OR $name == 'req') {
                $args[] =  $this->browser->request;
            }
            else if ($name == 'response' OR $name == 'resp') {
                $args[] =  $this->browser->response;
            }
            else if ($name == 'browser'){
                $args[] =  $this->browser;
            }
            else if ($name == 'app') {
                $args[] =  $this->browser->app;
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
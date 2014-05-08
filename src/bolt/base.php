<?php

namespace bolt;
use \b;


class base {

    /**
     * @var int
     */
    private $_guid = 9;

    /**
     * @var string
     */
    private $_env = 'dev';

    /**
     * @var array
     */
    private $_helpers = [];


    /**
     * Constructor
     *
     * @param array $helpers list of helper classes to register
     *
     * @return self
     */
    public function __construct($helpers=[]) {
        if (count($helpers)) { $this->helpers($helpers); }
    }


    /**
     * app
     */
    public function app($config = []) {
        if (isset($config['env'])) {
            $this->env($config['env']);
            unset($config['env']);
        }
        if (isset($config['helpers'])) {
            $this->helpers($config['helpers']);
            unset($config['helpers']);
        }
        return new application($config);
    }


    /**
     * set the env
     *
     * @param string $env name of env
     *
     */
    public function env($env=null) {
        return ($env === null ? $this->_env : $this->_env = $env);
    }


    /**
     * return a globally unique string
     *
     * @param string $prefix name of prefix
     *
     * @return string
     */
    public function guid($prefix='bolt') {
        return implode('', [$prefix, ($this->_guid++)]);
    }


    /**
     * call a helper method
     *
     * @param string $name name of helper class
     * @param array $args arguments to pass to help func
     *
     * @return mixed
     */
    public function __call($name, $args) {

        // loop through each helper and
        // figure out who can handle this method
        foreach ($this->_helpers as $key => $helper) {
            if (in_array($name, $helper['methods'])) {
                if (!$helper['instance']) {
                    $helper['instance'] = $this->_helpers[$key]['instance'] = $helper['ref']->newInstance();
                }
                return call_user_func_array([$helper['instance'], $name], $args);
            }
        }

        return false;
    }


    /**
     * attache helper classes to the global bolt instance
     *
     * @param string $class
     *
     */
    public function helpers($class) {
        if (is_array($class)) {
            foreach ($class as $name) {
                $this->helpers($name);
            }
            return true;
        }
        $ref = new \ReflectionClass($class);

        // already there
        if (array_key_exists($ref->name, $this->_helpers));

        $this->_helpers[$ref->name] = [
            'ref' => $ref,
            'methods' => array_map(function($m){ return $m->name; }, $ref->getMethods()),
            'instance' => false
        ];

        return $this;
    }


    /**
     * return all helpers
     * 
     * @return array
     */
    public function getHelpers() {
        return $this->_helpers;
    }


    /**
     * throw an exception class
     * 
     * @param  string $class
     * @param  string $message
     * @param  int $code
     *  
     * @return \Exception class
     */
    public function exepction($class, $message = null, $code = null) {
        $cn = '\bolt\exceptions\\'.$class;
        throw new $cn($message, $code);
    }

}
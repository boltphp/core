<?php
/**
 * bolt.php
 *
 * A PHP Framework
 *
 * @copyright  2010 - 2013
 * @author     Travis Kuhl (travis@kuhl.co)
 * @link       http://bolthq.com
 * @license    http://opensource.org/licenses/Apache-2.0 Apache 2.0
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */


// auto load
require __DIR__."/../vendor/autoload.php";

// set a global start time so we know
// how long this request takes
define('bStart', microtime(true));

// default to utc
date_default_timezone_set('UTC');

/**
 * BOLT!
 */
class bolt {
    const VERSION = '0.4.0';

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
     * inialize a new application interface
     *
     * @param array $config configuration array
     *
     * @return \bolt\application
     */
    public function init($config=[]) {

        if (isset($config['env'])) {
            self::env($config['env']);
        }

        // new application
        return new bolt\application($config);

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

    public function getHelpers() {
        return $this->_helpers;
    }

}


/**
 * define our static bolt oporator
 *
 */
class b {

    /**
     * @var b
     */
    private static $_instance;

    /**
     * @var array
     */
    public static $helpers = [
        '\bolt\helpers\base',
        '\bolt\helpers\classes',
        '\bolt\helpers\fs'
    ];

    /**
     * instance
     *
     * @return b
     */
    public static function instance() {
        if (!self::$_instance) {
            self::$_instance = new bolt(self::$helpers);
        }
        return self::$_instance;
    }


    /**
     * forward call onto the bolt instance
     *
     * @param string $name name of submodule
     * @param array $args list of args where args[0] is the function name
     *
     * @return mixed
     */
    public static function __callStatic($name, $args=[]){
        return call_user_func_array([b::instance(), $name], $args);
    }

};





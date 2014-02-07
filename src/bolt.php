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

//
class bolt {
    const VERSION = '0.4.0';

    /**
     * global guid counter
     */
    private static $_guid = 9;


    /**
     * name of environment
     */
    private static $_env = 'dev';

    /**
     * helpers holder
     */
    private static $_helpers = [];


    /**
     * inialize a new application interface
     *
     * @param $config array configuration array
     *
     * @return \bolt\application instance
     */
    public static function init($config) {

        // register some helpers
        self::helpers([
            '\bolt\helpers\classes',
            '\bolt\helpers\fs'
        ]);

        // new application
        return new bolt\application($config);

    }


    /**
     * set the env
     *
     * @param $env string name of env
     *
     */
    public static function env($env=null) {
        return self::$_env = $env;
    }


    /**
     * call a static function on a bolt class
     *
     * @param string $name name of submodule
     * @param array $args list of args where args[0] is the function name
     *
     * @return mixed submodule::func return or false for no sub module
     */
    public static function __callStatic($name, $args=[]){

        // loop through each helper and
        // figure out who can handle this method
        foreach (self::$_helpers as $key => $helper) {
            if (in_array($name, $helper['methods'])) {
                if (!$helper['instance']) {
                    $helper['instance'] = self::$_helpers[$key]['instance'] = $helper['ref']->newInstance();
                }

                return call_user_func_array([$helper['instance'], $name], $args);
            }
        }

    }


    /**
     * return a globally unique string
     *
     * @param $prefix string name of prefix
     *
     * @return string
     */
    public static function guid($prefix='bolt') {
        return implode('', [$prefix, (self::$_guid++)]);
    }


    /**
     * attache helper classes to the global bolt instance
     *
     * @param $class
     *
     */
    public static function helpers($class) {
        if (is_array($class)) {
            foreach ($class as $name) {
                self::helpers($name);
            }
            return true;
        }

        $ref = new \ReflectionClass($class);
        self::$_helpers[$ref->name] = [
            'ref' => $ref,
            'methods' => array_map(function($m){ return $m->name; }, $ref->getMethods()),
            'instance' => false
        ];

        return true;
    }

}

class b extends bolt {};
function b() {
    return b::instance();
}

define("bLoaded", true);
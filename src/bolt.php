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

class bolt {

    const VERSION = '0.3.1';

    private static $_instance = false;

    private static $_guid = 9;

    public static function instance($config=[]) {
        if (!self::$_instance) {
            self::$_instance = new bolt\base($config);
        }
        return self::$_instance;
    }

    // init
    public static function init($config) {
        return self::instance($config);
    }

    public static function env($env=null) {
        return self::instance()->env($env);
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


        if (self::instance()->helperExists($name)) {
            return self::instance()->callHelper($name, $args);
        }

        // name has a / in it
        if (isset($args[0]) AND stripos($args[0], '\\') !== false) {
            $parts = explode('\\', array_shift($args));
            array_unshift($args, array_pop($parts));
            array_unshift($parts, $name);
            $name = implode("_", $parts);
        }

        // is it a bolt class?
        $class = '\bolt\\'.str_replace("_", '\\', $name);

        // first of the args should be a function name
        $func = array_shift($args);

        // the function with the named args
        if (method_exists($class, $func)) {
            return call_user_func_array(array($class, $func), $args);
        }

    }

    public static function guid($prefix='bolt') {
        return implode('-', [$prefix, (self::$_guid++)]);
    }

}

class b extends bolt {};
function b() {
    return b::instance();
}

define("bLoaded", true);
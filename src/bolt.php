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

    const VERSION = '0.3';

    private static $_instance = false;

    public static function instance() {
        if (!self::$_instance) {
            self::$_instance = new bolt\base();
        }
        return self::$_instance;
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

        // passoff to our instance call method
        return self::instance()->call($name, $args);

    }

}

class b extends bolt {};
function b() {
    return b::instance();
}

define("bLoaded", true);
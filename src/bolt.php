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

// set a global start time so we know
// how long this request takes
define('bStart', microtime(true));

// default to utc
date_default_timezone_set(defined("bTimeZone") ? bTimeZone : 'UTC');

// utf8 bitches
\Patchwork\Utf8\Bootup::initAll();

/**
 *
 */
class bolt {


    /**
     * @var string
     */
    const VERSION = '0.6.1';

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
            self::$_instance = new bolt\base(self::$helpers);
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


    /**
     * inialize a new application interface
     *
     * @param array $config configuration array
     *
     * @return \bolt\application
     */
    public static function init($config=[]) {
        return self::instance()->app($config);
    }

}

/**
 * shortcut to bolt
 */
class b extends bolt {}

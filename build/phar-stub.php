<?php
/**
 * bolt.php
 *
 * A PHP Framework
 *
 * @copyright  2010 - 2014
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

Phar::mapPhar('bolt.phar');

require "phar://bolt.phar/vendor/autoload.php";
require "phar://bolt.phar/src/bolt.php";

__HALT_COMPILER();

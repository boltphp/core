<?php

require_once __DIR__."/../src/bolt.php";

date_default_timezone_set('UTC');

define("VENDOR_DIR", realpath(__DIR__."/../vendor/"));
define("MOCK_DIR", realpath(__DIR__."/mock"));


// for some reason travis-ci has problems
// autoloading this file. so we'll manually
// include it to pull it into
require VENDOR_DIR."/leafo/lessphp/lessc.inc.php";


class Test extends PHPUnit_Framework_TestCase {


}

bolt::init([]);

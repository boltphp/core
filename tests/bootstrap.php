<?php

require_once __DIR__."/../src/bolt.php";

date_default_timezone_set('UTC');

define("MOCK_DIR", realpath(__DIR__."/mock"));

class Test extends PHPUnit_Framework_TestCase {


}

bolt::init([]);

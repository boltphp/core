<?php

require_once __DIR__."/../src/bolt.php";

date_default_timezone_set('UTC');

define("VENDOR_DIR", realpath(__DIR__."/../vendor/"));
define("MOCK_DIR", realpath(__DIR__."/mock"));

define("TEST_ROOT", realpath(__DIR__));

class Test extends PHPUnit_Framework_TestCase {

    protected function eq() {
        return call_user_func_array([$this, 'assertEquals'], func_get_args());
    }

}

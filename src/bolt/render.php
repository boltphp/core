<?php

namespace bolt;
use \b;

class render {

    public static $engines = [];

    public static function setEngines($engines) {
        self::$engines = $engines;
    }

    public static function string() {

    }

    public static function file($config) {
        return 'a';
    }

}
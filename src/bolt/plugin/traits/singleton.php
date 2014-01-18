<?php

namespace bolt\plugin\traits;

trait singleton {

    private static $_instance = false;

    public static function instance($config=[]) {
        if (!self::$_instance) {
            $class = __CLASS__;
            self::$_instance = new $class($config);
        }
        return self::$_instance;
    }

}
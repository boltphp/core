<?php

namespace bolt\plugin;

trait singletonTraits {

    private static $_instance = false;

    public static function instance() {
        if (!self::$_instance) {
            $class = __CLASS__;
            self::$_instance = new $class;
        }
        return self::$_instance;
    }

}
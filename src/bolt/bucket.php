<?php

namespace bolt;
use \b;


use Symfony\Component\PropertyAccess\PropertyAccess;

abstract class bucket implements bucket\face {

    private static $_access = false;

    public static function access() {
        if (!self::$_access) {
            self::$_access = PropertyAccess::createPropertyAccessorBuilder()
                ->disableExceptionOnInvalidIndex()
                ->getPropertyAccessor();
        }
        return self::$_access;
    }

    public static function create($value, $parent=false) {

        // type
        $type = substr(gettype($value), 0, 1);

        // can we handle this
        if (class_exists('bolt\bucket\\'.$type, true)) {
            $class = 'bolt\bucket\\'.$type;
            return new $class($value, $parent);
        }

    }

}
<?php

namespace bolt;
use \b;

abstract class bucket implements bucket\face {

    public static function create($value) {

        // type
        $type = substr(gettype($value), 0, 1);

        // can we handle this
        if (class_exists('bolt\bucket\\'.$type, true)) {
            $class = 'bolt\bucket\\'.$type;
            return new $class($value);
        }

    }

}
<?php

namespace bolt\render;
use \b;

class engine {

    public static function collect() {
        $engines = [];

        // collect all engines
        foreach (b::getClassImplements('\bolt\render\engine\face') as $class) {
            $engines[$class->getConstant('EXT')] = $class;
        }

        return $engines;

    }

}
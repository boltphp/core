<?php

namespace bolt\render;
use \b;


class engine {

    public static function collect() {

        // collect all engines
        foreach (b::getClassImplements('\bolt\render\engine\face') as $class) {
            if ($class->name === 'bolt\render\engine\base') {continue;}
            $ext = $class->getConstant('EXT');
            if (!b::render('hasEngine', $ext)) {
                b::render('setEngine', $ext, $class);
            }
        }

    }

}
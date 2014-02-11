<?php

namespace bolt\helpers;
use \b;


class base {

    public function mergeArray($a1, $a2) {
        if (!is_array($a1)) { $a1 = array(); }
        if (!is_array($a2)) { $a2 = array(); }
        foreach ( $a2 as $k => $v ) {
            if ( array_key_exists($k, $a1) AND is_array($v) ) {
                $a1[$k] = self::mergeArray($a1[$k], $a2[$k]);
            }
            else {
                $a1[$k] = $v;
            }
        }
        return $a1;
    }

}
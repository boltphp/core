<?php

namespace bolt\helpers;
use \b;

/**
 * base helpers class
 */
class base {

    /**
     * check for a variable in an array
     *
     * @param string $key
     * @param mixed $default
     * @param array $array
     * @param int $filter
     *
     * @return mixed
     */
    public function param($key, $default = null, array $array, $filter = null) {
        if (!is_array($array)) {return $default;}
        $resp = array_key_exists($key, $array) ? $array[$key] : $default;
        if ($filter !== null) {
            $resp = filter_var($resp, $filter);
        }
        return $resp;
    }


    /**
     * merge two arrays recursivly
     *
     * @param array $a1
     * @param array $a2
     *
     * @return array
     */
    public function mergeArray(array $a1, array $a2) {
        foreach ( $a2 as $k => $v ) {
            if ( array_key_exists($k, $a1) && is_array($v) ) {
                $a1[$k] = $this->mergeArray($a1[$k], $a2[$k]);
            }
            else {
                $a1[$k] = $v;
            }
        }
        return $a1;
    }

}
<?php

namespace bolt\plugin;
use \b;


/**
 * factory interface
 */
interface factory {

    /**
     * factory function interface
     *
     * @param array $config
     */
    public static function factory($parent, $config = []);

}
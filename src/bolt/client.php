<?php

namespace bolt;

/**
 * bolt client
 */
class client {

    /**
     * bind the client comamnds to the
     * provided bolt application
     * 
     * @param  bolt\application $app
     * 
     * @return void
     */
    public static function bind(application $app) {

        if (!$app->pluginExists('cli')) {
            $app->plug('cli', '\bolt\cli');
        }

        $app['cli']->plug([
                
                ['compile', 'bolt\client\compile'],
                ['models', 'bolt\client\models']

            ]);

    }

}
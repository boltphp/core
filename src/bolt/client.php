<?php

namespace bolt;


class client {

    public static function bind(application $app) {

        if (!$app->pluginExists('cli')) {
            $app->plug('cli', '\bolt\cli');
        }


        $app['cli']->plug([

                ['build', 'bolt\client\build']

            ]);

    }

}
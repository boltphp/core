<?php

namespace bolt;

class client {

    public static function bind(application $app) {

        $app['cli']->plug([

                ['build', 'bolt\client\build']

            ]);

    }

}
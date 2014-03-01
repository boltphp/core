<?php

namespace bolt\client;
use \b;


class build extends command {

    public static $name = "build";

    public static $configure = [
        'arguments' => [
            'package' => [
                'mode' => self::REQUIRED,
                'description' => "Package file to build"
            ]
        ]
    ];


    // build
    public function call($pacakge = '.') {

        $this->writeln('poop');

    }

}
<?php

namespace bolt\client;
use \b;


class build extends command {

    public static $name = "build";

    public static $configuration = [


    ];


    // build
    public function call() {

        $this->writeln('poop');

    }

}
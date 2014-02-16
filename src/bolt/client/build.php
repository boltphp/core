<?php

namespace bolt\client;
use \b;



class build extends bolt\cli\command {

    private $_client;

    public function __construct(\bolt\client $client) {
        $this->_client = $client;
        parent::__construct();
    }

    public function configure() {

        $this
            ->setName("build")
            ->setDescription("Build a bolt application into a deployable package");



    }

}
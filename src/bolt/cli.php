<?php

namespace bolt;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Application as ConsoleApplication;

class cli extends plugin {
    use events;

    private $_console;

    public function __construct() {

        $this->_console = new ConsoleApplication();
    }

}
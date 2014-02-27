<?php

namespace bolt;

use \Symfony\Component\Console\Application as ConsoleApplication,
    \Symfony\Component\Console\Input\ArgvInput,
    \Symfony\Component\Console\Output\ConsoleOutput;

class cli extends plugin {
    use events;

    private $_app;

    private $_config = [];

    private $_input;
    private $_output;

    private $_console;

    public function __construct(application $app, $config = []) {
        $this->_app = $app;
        $this->_config = $config;

        $this->_app->on('run:cli', [$this, 'execute']);

        // loop through each plugin and add it to
        $this->_console = new ConsoleApplication();

    }

    public function getApp() {
        return $this->_app;
    }

    public function getConsole() {
        return $this->_console;
    }

    public function getInput(){
        return $this->_input;
    }

    public function getOutput() {
        return $this->_output;
    }

    public function execute() {


        $this->_input = new ArgvInput();
        $this->_output = new ConsoleOutput();

        foreach ($this->getPlugins() as $plug) {
            $i = $plug['instance'];
            $this->_console->add($i);
        }

        $this->_console->setAutoExit(false);

        return $this->_console->run($this->_input, $this->_output);

    }

}
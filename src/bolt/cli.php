<?php

namespace bolt;

use \Symfony\Component\Console\Application as ConsoleApplication,
    \Symfony\Component\Console\Input\ArgvInput,
    \Symfony\Component\Console\Output\ConsoleOutput,
    \Symfony\Component\EventDispatcher\EventDispatcher,
    \Symfony\Component\Console\ConsoleEvents;



class cli extends plugin {

    private $_app;

    private $_config = [];

    private $_input;
    private $_output;

    private $_console;

    private $_dispatch;

    public function __construct(application $app, $config = []) {
        $this->_app = $app;
        $this->_config = $config;

        $this->_app->on('run:cli', [$this, 'execute']);

        if (!isset($config['argv'])) {
            $config['argv'] = isset($_SERVER['argv']) ? $_SERVER['argv'] : [];
        }

        // loop through each plugin and add it to
        $this->_console = new ConsoleApplication();

        $this->_dispatch = new EventDispatcher();

        $this->_console->setDispatcher($this->_dispatch);


        $this->_input = new ArgvInput($config['argv']);
        $this->_output = new ConsoleOutput();

    }

    public function on($e, \Closure $cb) {
        switch($e) {
            case 'command':
                $e = ConsoleEvents::COMMAND; break;
            case 'terminate':
                $e = ConsoleEvents::TERMINATE; break;
            case 'exception':
                $e = ConsoleEvents::EXCEPTION; break;
        }
        $this->_dispatch->addListener($e, $cb);
        return $this;
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

        foreach ($this->getPlugins() as $plug) {
            $i = $plug['instance'];
            $this->_console->add($i);
        }

        $this->_console->setAutoExit(false);

        return $this->_console->run($this->_input, $this->_output);

    }

}
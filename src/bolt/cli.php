<?php

namespace bolt;

use \Symfony\Component\Console\Application as ConsoleApplication,
    \Symfony\Component\Console\Input\ArgvInput,
    \Symfony\Component\Console\Output\ConsoleOutput,
    \Symfony\Component\EventDispatcher\EventDispatcher,
    \Symfony\Component\Console\ConsoleEvents;


/**
 * CLI client plugin
 */
class cli extends plugin {

    /**
     * application
     * 
     * @var \bolt\application
     */
    private $_app;

    /**
     * config options
     * 
     * @var array
     */
    private $_config = [];

    /**
     * console input
     * 
     * @var Symfony\Component\Console\Input\ArgvInput
     */
    private $_input;

    /**
     * console output controller
     * 
     * @var Symfony\Component\Console\Output\ConsoleOutput
     */
    private $_output;

    /**
     * symfony console application
     * 
     * @var Symfony\Component\Console\Application
     */
    private $_console;

    /**
     * event disbatch object
     * 
     * @var Symfony\Component\EventDispatcher\EventDispatcher
     */
    private $_dispatch;


    /**
     * Constructor
     * 
     * @param bolt\application $app
     * @param array $config
     */
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


    /**
     * subscrube to console events
     * 
     * @param  string $e
     * @param  Closure $cb
     *  
     * @return self
     */
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


    /**
     * get the bolt app
     * 
     * @return \bolt\application
     */
    public function getApp() {
        return $this->_app;
    }


    /**
     * get the symfony console
     * 
     * @return Symfony\Component\Console\Application
     */
    public function getConsole() {
        return $this->_console;
    }


    /**
     * return console input 
     * @return Symfony\Component\Console\Output\ConsoleOutput
     */
    public function getInput(){
        return $this->_input;
    }


    /**
     * get console output 
     * @return Symfony\Component\Console\Output\ConsoleOutput
     */
    public function getOutput() {
        return $this->_output;
    }


    /**
     * execute the cli application
     * 
     * @return int
     */
    public function execute() {

        foreach ($this->getPlugins() as $plug) {
            $i = $plug['instance'];
            $this->_console->add($i);
        }

        $this->_console->setAutoExit(false);

        return $this->_console->run($this->_input, $this->_output);

    }

}
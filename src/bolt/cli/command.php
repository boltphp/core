<?php

namespace bolt\cli;
use \b;

use \Symfony\Component\Console\Command\Command as SymfonyCommand,
    \Symfony\Component\Console\Input\InputArgument,
    \Symfony\Component\Console\Input\InputOption,
    \Symfony\Component\Console\Input\InputInterface,
    \Symfony\Component\Console\Output\OutputInterface;

class command extends SymfonyCommand {

    static $name = "command";

    const OPTIONAL = InputArgument::OPTIONAL;

    private $_cli;

    final public function __construct(\bolt\cli $cli) {
        $this->_cli = $cli;

        if (!property_exists($this, 'ns')) {
            throw new \Exception("Commands must define a namespace");
        }

        $this->setName($this::$name);

        // parent
        parent::__construct();

        $this->setApplication($cli->getConsole());

        if (property_exists($this, 'configure')) {
            foreach ($this::$configure as $name => $value) {
                switch($name) {
                    case 'description': $this->setDescription($value); break;
                    case 'options':
                        foreach ($value as $name => $opt) {
                            $opt['name'] = $name;
                            $this->_addOption($opt);
                        }
                        break;
                    case 'arguments':
                        foreach ($value as $name => $opt) {
                            $opt['name'] = $name;
                            $this->_addArgument($opt);
                        }
                        break;
                }
            }
        }


        $this->init();
    }

    public function __get($name) {
        switch($name) {
            case 'app':
                return $this->_cli->getApp();
            case 'cli':
                return $this->_cli;
        };
        return null;
    }

    public function setName($name) {
        parent::setName(implode(":", [$this::$ns, $name]));
        return $this;
    }

    private function _addOption($opt) {
        $this->addOption(
                $opt['name'],
                b::param('shortcut', null, $opt),
                b::param('mode', null, $opt),
                b::param('description', null, $opt),
                b::param('default', null, $opt)
            );
        return $this;
    }

    private function _addArgument($opt) {
        $this->addArgument(
            $opt['name'],
            b::param('mode', null, $opt),
            b::param('description', null, $opt),
            b::param('default', null, $opt)
        );
        return $this;
    }

    public function writeln() {
        call_user_func_array([$this->_cli->getOutput(), 'writeln'], func_get_args());
        return $this;
    }

    public function arg($name) {
        return $this->_cli->getInput()->getArgument($name);
    }

    public function opt($name) {
        return $this->_cli->getInput()->getOption($name);
    }


    public function init() {

    }

    public function execute(InputInterface $input, OutputInterface $output) {

        $this->call();
    }

}
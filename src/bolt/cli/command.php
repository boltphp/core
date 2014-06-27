<?php

namespace bolt\cli;
use \b;

use Symfony\Component\Console\Command\Command as SymfonyCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Console\Question\ConfirmationQuestion,
    Symfony\Component\Console\Question\Question,
    Symfony\Component\Console\Question\ChoiceQuestion,
    Symfony\Component\Console\Helper\ProgressBar;


class command extends SymfonyCommand {

    static $name = "command";

    //
    const REQUIRED = InputArgument::REQUIRED;
    const OPTIONAL = InputArgument::OPTIONAL;
    const IS_ARRAY = InputArgument::IS_ARRAY;

    const VALUE_NONE     = InputOption::VALUE_NONE;
    const VALUE_REQUIRED = InputOption::VALUE_REQUIRED;
    const VALUE_OPTIONAL = InputOption::VALUE_OPTIONAL;
    const VALUE_IS_ARRAY = InputOption::VALUE_IS_ARRAY;

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

        $config = [];

        if (property_exists($this, 'configure')) {
            $config = $this::$configure;
        }
        else if (method_exists($this, 'getConfigure')) {
            $config = static::getConfigure();
        }

        if (count($config) > 0) {
            foreach ($config as $name => $value) {
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
                    case 'aliases':
                        $this->setAliases($value);
                        break;
                };
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
            case 'output':
                return $this->_cli->getOutput();
            case 'input':
                return $this->_cli->getInput();
        };
        return null;
    }

    public function get($name) {
        switch($name) {
            case 'progressbar':
                return $this->ProgressBar();

            case 'progress':
            case 'dialog':
            case 'formatter':
            case 'table':
            case 'question':
                return $this->_cli->getConsole()->getHelperSet()->get($name);

        };
        return null;
    }

    public function progressbar($units) {
        return new ProgressBar($this->output, $units);
    }

    public function ask($q) {
        return $this->get('question')->ask($this->input, $this->output, $q);
    }

    public function askQuestion($text) {
        return $this->ask($this->question($text));
    }

    public function askConfirmation($text, $default = true) {
        return $this->ask($this->confirmation($text, $default));
    }

    public function question($text) {
        return new Question(trim($text)." ");
    }

    public function confirmation($text, $default = true) {
        return new ConfirmationQuestion(trim($text)." ", $default);
    }

    public function choice($text, $choices, $default) {
        return new ChoiceQuestion(trim($text)." ", $choices, $default);
    }

    public function setName($name) {
        parent::setName(implode(":", [$this::$ns, $name]));
        return $this;
    }

    public function setAliases($aliases) {
        parent::setAliases(array_map(function($name){ return implode(":", [$this::$ns, $name]); }, $aliases));
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

    public function writeError($msg) {
        return $this->writeln('<error>'.$msg.'</error>');
    }

    public function arg($name) {
        return $this->_cli->getInput()->getArgument($name);
    }

    public function opt($name) {
        return $this->_cli->getInput()->getOption($name);
    }


    public function init() {

    }

    public function setup() {

    }

    public function execute(InputInterface $input, OutputInterface $output) {
        $method = 'call';

        // stup
        $this->setup();

        $args = $input->getArguments();


        if ($input->hasArgument('cmd')) {
            $method = $input->getArgument('cmd');

            if (stripos($method, ':') !== false) {
                $method = lcfirst(implode("", array_map(function($val){
                    return ucfirst($val);
                }, explode(":", $method))));
            }
        }

        if (!method_exists($this, $method)) {
            return false;
        }

        $ref = new \ReflectionMethod($this, $method);
        $params = [];

        foreach ($ref->getParameters() as $param) {
            if ($input->hasArgument($param->name)) {
                $params[] = $input->getArgument($param->name) ?: $param->getDefaultValue();
            }
            else if ($input->hasOption($param->name)) {
                $params[] = $input->getOption($param->name) ?: $param->getDefaultValue();
            }
        }

        return call_user_func_array([$this, $method], $params);
    }

}
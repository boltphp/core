<?php

namespace bolt;


class client extends cli {

    private $_app = false;

    private $_cli;

    public function __construct(application $app) {
        $this->_app = $app;

        // command
        $this->plug([
            // ['compile', 'bolt\client\compile'],
            ['build', 'bolt\client\build'],
            // ['deploy', 'bolt\client\deploy']
        ]);

        $this->_console = new ConsoleApplication();

        // get all plugins
        foreach ($this->getPlugins() as $name => $plugin) {
            $this->_console->add($this[$name]);
        }

    }


    public function run() {



        var_dump('x'); die;

    }

}
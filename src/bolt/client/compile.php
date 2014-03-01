<?php

namespace bolt\client;
use \b;


class compile extends command {

    public static $name = "compile";

    public static $configure = [
        'arguments' => [
            'cmd' => [
                'mode' => self::REQUIRED
            ]
        ]
    ];

    private $_dir = false;
    private $_loaders = [];

    public function init() {

        // don't use anything compield
        $this->app->useCompiled(false);

        // open our composer package
        $composer = $this->app->getComposerFile();

        // we need a composer file
        if (!$composer) {
            return $this->writeError("Unable to file composer.json file.");
        }

        $dir = b::path($composer['dir'], 'compiled');

        $this->_dir = $dir;


    }

    // build
    public function generate() {

        $this->clean();

        $dir = $this->_dir;

        // get all events
        $listeners = $this->app->getListeners('compile');

        $prog = $this->get('progress');

        $prog->start($this->output, count($listeners));

        foreach ($listeners as $item) {
            $prog->clear();

            $this->app->executeListener($item, [
                    'dir' => $dir,
                    'client' => $this
                ]);

            $prog->display();
            $prog->advance();
        }

        $prog->finish();

        file_put_contents("{$this->_dir}/loader.php", '<?php return '.var_export($this->_loaders, true).';');

        // done
        $this->writeln('Done');

    }

    public function clean() {
        // no dir
        if (is_dir($this->_dir)) {
            b::fs('remove', $this->_dir);
        }
        b::fs('mkdir', $this->_dir);

    }


    public function saveCompileLoader($name, $data) {
        if (!$this->_dir) {
            throw new \Exception('No compiled directory provided');
        }
        if ($name == 'loaders') {
            throw new \Exception("Loader name can not be 'loaders' ");
        }
        $var = [
            'created' => time(),
            'name' => $name,
            'data' => $data
        ];
        file_put_contents("{$this->_dir}/{$name}.php", '<?php return '.var_export($var, true).';');
        $this->_loaders[] = $name;
    }

    public function makeDir($name) {
        if (!$this->_dir) {
            throw new Exception("No compiled directory");
        }
        $dir = b::path($this->_dir, $name);
        if (is_dir($dir)) { b::fs('remove', $dir); }
        b::fs('mkdir', $dir);
        return $dir;
    }

}
<?php

namespace bolt\client;

use b,
    Symfony\Component\Filesystem\Filesystem
;

class compile extends command {

    public static $name = "compile";

    public static function getConfigure() {
        return [
            'arguments' => [
                'cmd' => [
                    'mode' => self::REQUIRED
                ]
            ],
            'options' => [
                'plugins' => [
                    'mode' => self::VALUE_IS_ARRAY | self::VALUE_REQUIRED
                ],
                'dir' => [
                    'mode' => self::VALUE_REQUIRED
                ]
            ]
        ];
    }

    private $_composerDir = false;
    private $_dir = false;
    private $_loaders = [];

    public function init() {

        // disable opcaches
        ini_set('opcache.enable', 0);
        ini_set('apc.enabled', 0);

        // if we have a compiled plugin
        // disable it
        $this->app->pluginExists('compiled', function(){
            $this->disable();
        });

        // open our composer package
        $composer = $this->app->getComposerFile();

        // we need a composer file
        if (!$composer) {
            return $this->writeError("Unable to find composer.json file.");
        }

        $dir = b::path($composer['dir'], 'compiled');

        // compile dir
        $this->_dir = $dir;
        $this->_composerDir = b::path($composer['dir']);

        // make sure they've defied a bootstrap dir
        // in their app config
        if (!$this->app->getBootstrapDir()) {
            return $this->writeError("You must a bootstrap directory defined in your app config.");
        }

    }

    // build
    public function generate() {
        $plugins = $this->opt('plugins');

        $this->_dir = $dir = $this->opt('dir') ? b::path(getcwd(), $this->opt('dir')) : $this->_dir;

        $this->clean();

        // get all events
        $listeners = $this->app->getListeners('compile');

        $prog = $this->get('progress');

        $prog->start($this->output, count($listeners));


        foreach ($listeners as $item) {
            $prog->clear();

            $class = get_class($item->callback[0]);

            if ($plugins && !in_array($class, $plugins)) {
                $this->writeln("Skipped {$class}");
                continue;
            }

            try {
                $this->app->executeListener($item, [
                        'dir' => $dir,
                        'client' => $this
                    ]);
            }
            catch (\Exception $e) {
                throw new \Exception("Unable to run compile command (Error: {$e->getMessage()}).");
            }

            $prog->display();
            $prog->advance();
        }

        $prog->finish();

        $uid = uniqid("BoltCompiled");


        $fs = new Filesystem();
        $rel = $fs->makePathRelative(realpath($this->_dir), $this->_composerDir);

        $config = [
            'loaders' => $this->_loaders,
            'dir' => "../{$rel}"
        ];


        $this->app->fire("compile:complete", ['config' => $config]);

        $sub = '<'.'?php
            class '.$uid.' implements \bolt\plugin\singleton {
                private $_enabled = true;
                private $_app = false;
                private $_config = [];
                public function __construct(\bolt\application $app, $config = []) {
                    $this->_app = $app;
                    $this->_config = $config;
                }
                public function setDir($dir) {
                    $this->_config["dir"] = $dir;
                    return $this;
                }
                public function enable() {
                    $this->_enabled = true;
                    return $this;
                }
                public function disable() {
                    $this->_enabled = false;
                    return $this;
                }
                public function exists($name) {
                    return $this->_enabled ? array_key_exists($name, $this->_config["loaders"]) : false;
                }
                public function get($name, \Closure $cb = null) {
                    if (!$this->_enabled) {return false;}
                    if (!$this->exists($name)) {return false;}
                    return $cb ? call_user_func($cb, $this->_config["loaders"][$name]) : $this->_config["loaders"][$name];
                }
                public function getFile($path) {
                    $path = b::path($this->_app->path($this->_config["dir"]), $path);
                    return file_exists($path) ? file_get_contents($path) : null;
                }
                public function getFilePath($path) {
                    return b::path($this->_app->path($this->_config["dir"]), $path);
                }
            }
            return function($app) {
                $app->plug("compiled", "'.$uid.'", '.var_export($config, true).');
            };
        ';


        file_put_contents("{$this->app->getBootstrapDir()}/compiled.php", $sub);

        // done
        $this->writeln('Done');

    }

    public function clean() {
        // no dir
        if (is_dir($this->_dir)) {
            b::fs('remove', $this->_dir);
        }
        b::fs('remove', "{$this->app->getBootstrapDir()}/compiled.php");
        b::fs('mkdir', $this->_dir);
    }


    public function saveCompileLoader($name, $data) {
        if (!$this->_dir) {
            throw new \Exception('No compiled directory provided');
        }
        if ($name == 'loaders') {
            throw new \Exception("Loader name can not be 'loaders' ");
        }
        $this->_loaders[$name] = [
            'created' => time(),
            'name' => $name,
            'data' => $data
        ];
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
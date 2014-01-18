<?php

namespace bolt\browser;
use \b;

use Symfony\Component\Finder\Finder;


class controller {
    use \bolt\plugin;

    protected $layout = null;

    private $_parameters = [];

    private $_browser;

    public function init() {

    }

    public function __set($name, $value) {
        $this->_parameters[$name] = $value;
    }

    public function model($class, $config=[]) {

        // nope
        if (!class_exists($class, true)) {
            var_dump('bad model class');
            return false;
        }

        // we need a source manager
        $source = $this->source;

        // no source we stop
        if (!$source OR !is_a($source, 'bolt\source')) {
            var_dump('bad');
            return false;
        }

        return new $class($source, $config);

    }

    public function view($file, $vars=[], $paths=[]) {

        // loop through vars and
        // print our params
        foreach ($this->_parameters as $key => $value) {
            if (!array_key_exists($key, $vars)) {
                $vars[$key] = $value;
            }
        }

        // paths to find
        $paths += b::settings('browser.paths.views')->value;

        // find this template
        $find = new Finder();

        $base = pathinfo($file)['dirname'];
        $name = pathinfo($file)['basename'];

        // loop through each path
        foreach ($paths as $path) {
            $files = $find->files()->in(b::path($path, $base))->name($name);
            if (iterator_count($files)) {
                $it = iterator_to_array($files);
                $first = array_shift($it);

                return new view([
                    'file' => $first->getRealPath(),
                    'vars' => $vars,
                    'context' => $this,
                    'helpers' => [

                    ]
                ]);
            }
        }

        // nope
        return false;

    }

    protected function getArgsFromMethodRef($ref, $params) {

        // we need to get all args
        // from the function and see what we can prefill
        $args = [];

        foreach ($ref->getParameters() as $param) {
            if ($param->getClass() AND $param->getClass()->name === 'bolt\browser\request') {
                $args[] = $this->request;
            }
            else if ($param->getClass() AND $param->getClass()->name === 'bolt\browser\response') {
                $args[] = $this->response;
            }
            else if (array_key_exists($param->getName(), $params)) {
                $args[] = $params[$param->getName()];
            }
            else if ($param->isOptional()) {
                $args[] = $param->getDefaultValue();
            }
            else if ($param->isOptional()) {
                $args[] = null;
            }
        }

        return $args;
    }

}
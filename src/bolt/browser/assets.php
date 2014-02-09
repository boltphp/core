<?php

namespace bolt\browser;
use \b;


use Assetic\FilterManager;
use Assetic\AssetManager;
use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;
use Assetic\Asset\GlobAsset;
use Assetic\Asset\HttpAsset;
use Assetic\Asset\StringAsset;

use Assetic\Filter\CssRewriteFilter;


class assets implements \bolt\plugin\singleton {

    private $_browser;

    private $_config = [];

    private $_dirs = [];

    private $_manager;

    private $_filters = ['*' => []];


    private $_output = [
        'script' => [],
        'style' => []
    ];

    public function __construct(\bolt\browser $browser, $config=[]) {
        $this->_browser = $browser;
        $this->_config = $config;
        $this->_dirs = isset($config['dirs']) ? $config['dirs'] : [];
    }


    public function add($type, $config=false) {
        if (is_array($type) AND !$config) {
            foreach ($type as $item) {
                $this->add($item[0], $item[1]);
            }
            return $this;
        }

        $o = $type == 'script' ? new assets\script($this) : new assets\style($this);

        if (is_string($config)) {
            $o->setPath($config);
        }
        else if (is_array($config)) {
            foreach ($config as $k => $v) {
                call_user_func([$o, "set{$k}"], $v);
            }
        }
        $this->_output[$type][] = $o;
        return $o;
    }


    public function find($file) {

        foreach ($this->_dirs as $dir) {
            $path = $this->_browser->path($dir, $file);
            if (file_exists($path)) {
                return [
                    'path' => $path,
                    'rel' => $this->_browser->path($dir)
                ];
            }
        }

        return false;
    }


}
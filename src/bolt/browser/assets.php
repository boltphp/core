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

        if (isset($config['path'])) {
            $browser->bind('assets', 'bolt\browser\middleware\assets', $config);
        }
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

    public function out($group, $type=false) {
        $tag = [];

        if (!$type OR $type == 'script') {
            foreach ($this->_output['script'] as $script) {
                if ($script->inGroup($group)) {
                    $tag[] = $script->out();
                }
            }
        }

        if (!$type OR $type == 'style') {
            foreach ($this->_output['style'] as $leaf) {
                if ($leaf->inGroup($group)) {
                    $tag[] = $leaf->out();
                }
            }
        }

        return implode("\n", $tag);

    }


    public function stylesheet($leafs) {
        if (is_string($leafs)) { $leafs = [$leafs]; }

        $output = [];

        foreach ($leafs as $leaf) {
            $output[] = str_replace('{path}', "{$leaf}", rtrim($this->_config['path'],'/'));
        }

        return implode("\n", array_map(function($href) {
            return '<link rel="stylesheet" href="'.$href.'" type="text/css">';
        }, $output));

    }


}
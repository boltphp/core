<?php

namespace bolt\browser;
use \b;

use Assetic\FilterManager;
use Assetic\AssetManager;
use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;
use Assetic\Asset\GlobAsset;
use Assetic\Asset\HttpAsset;


class assets implements \bolt\plugin\singleton {

    private $_manager = false;

    private $_browser = false;

    public function __construct() {
        $this->_manager = new AssetManager();
    }

    public function bind(\bolt\browser $browser, $route='/a/{path}') {
        $this->_browser = $browser;

        // register our asset path
        if ($route !== false AND stripos($route, '{path}') !== false) {
            $this->_browser->get($route, '\bolt\browser\controller\asset', [
                    'requirements' => ['path' => '.*']
                ]);
        }

    }

    public function stylesheet($leafs) {
        if (is_string($leafs)) { $leafs = [$leafs]; }

        $output = [];

        foreach ($leafs as $leaf) {
            $output[] = $leaf;
        }

        return implode("\n", array_map(function($href) {
            return '<link rel="stylesheet" href="'.$href.'" type="text/css">';
        }, $output));

    }

}
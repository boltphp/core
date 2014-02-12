<?php

namespace bolt\browser\assets;
use \b;

use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;
use Assetic\Asset\GlobAsset;

class group {

    private $_assets;
    private $_name;
    private $_type;

    private $_col;

    public function __construct(\bolt\browser\assets $assets, $name, $type)  {
        $this->_assets = $assets;
        $this->_name = $name;
        $this->_type = $type;

        $this->_col = new AssetCollection();
    }

    public function getType() {
        return $this->_type;
    }

    public function getName() {
        return $this->_name;
    }

    public function add($files) {

        if (is_string($files)) {
            $f = $this->_assets->find($files);
            $this->_col->add(new FileAsset($f['path'], [], $f['rel']));
        }
        else if (is_array($files)) {
            foreach ($files as $file) {
                $f = $this->_assets->find($file);
                $this->_col->add(new FileAsset($f['path'], [], $f['rel']));
            }
        }

        return $this;
    }


    public function getComboUrl() {
        $parts = [];

        foreach ($this->_col->all() as $file) {
            $parts[] = $file->getSourcePath();
        }

        return $this->_assets->url(implode("&", $parts));

    }

    public function appendToDom($dom, $to, $combo=null) {
        if ($combo === null) { $combo = b::env() !== 'dev'; }

        if ($this->_type == 'script') {
            if ($combo) {
                $to->append($dom->create("script", null, [
                        'src' => $this->getComboUrl()
                    ]));
            }
            else {
                foreach ($this->_col->all() as $file) {
                    $to->append($dom->create("script", null, [
                            'src' => $this->_assets->url($file->getSourcePath())
                        ]));
                }
            }
        }
        else {
            if ($combo) {
                $to->append($dom->create("link", null, [
                        'type' => 'text/css',
                        'rel' => 'stylesheet',
                        'href' => $this->getComboUrl()
                    ]));
            }
            else {
                foreach ($this->_col->all() as $file) {
                    $to->append($dom->create("link", null, [
                            'type' => 'text/css',
                            'rel' => 'stylesheet',
                            'href' => $this->_assets->url($file->getSourcePath())
                        ]));
                }
            }
        }
    }

}
<?php

namespace bolt\http\assets;
use \b;

use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;
use Assetic\Asset\GlobAsset;
use Assetic\Asset\HttpAsset;

class group implements \IteratorAggregate {

    private $_assets;
    private $_name;
    private $_type;

    private $_col;

    public function __construct(\bolt\http\assets $assets, $name, $type)  {
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

    public function count() {
        return count($this->_col->all());
    }

    public function add($files) {

        if (is_string($files) && (stripos($files, 'http') !== false || strpos($files, '//') === 0)) {
            $this->_col->add(new HttpAsset($files));
        }
        else if (is_string($files)) {
            $f = $this->_assets->find($files);
            if ($f) {
                $this->_col->add(new FileAsset($f['path'], [], $f['rel']));
            }
        }
        else if (is_a($files, 'SplFileInfo')) {
            // figure out which root this file is
            $dir = $this->_assets->findDir($files->getRealPath());
            $this->_col->add(new FileAsset($files->getPathName(), [], $dir));
        }
        else if (is_a($files, 'bolt\helpers\fs\glob')) {
            foreach ($files as $file) {
                $this->add($file);
            }
        }
        else if (is_array($files)) {

            foreach ($files as $file) {
                $this->add($file);
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

    public function appendToDom($dom, $to, $combo=true, $attr = []) {
        // if ($combo === null) { $combo = b::env() !== 'dev'; }

        if ($this->_type == 'script') {
            if ($combo) {
                $attr['src'] = $this->getComboUrl();
                $to->append($dom->create("script", null, $attr));
            }
            else {
                foreach ($this->_col->all() as $file) {
                    $attr['src'] = $this->_assets->url($file);

                    $to->append($dom->create("script", null, $attr));
                }
            }
        }
        else {
            if ($combo) {
                $attr += [
                    'type' => 'text/css',
                    'rel' => 'stylesheet',
                    'href' => $this->getComboUrl()
                ];
                $to->append($dom->create("link", null, $attr));
            }
            else {
                foreach ($this->_col->all() as $file) {
                    $attr['type'] = 'text/css';
                    $attr['rel'] = 'stylesheet';
                    $attr['href'] = $this->_assets->url($file);
                    $to->append($dom->create("link", null, $attr));
                }
            }
        }
    }

    public function getIterator() {
        return $this->_col;
    }

}
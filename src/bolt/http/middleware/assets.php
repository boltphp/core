<?php

namespace bolt\http\middleware;
use \b;

use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;
use Assetic\Asset\GlobAsset;
use Assetic\Asset\StringAsset;
use Assetic\Asset\AssetCache;
use Assetic\Cache\FilesystemCache;


class assets extends \bolt\http\middleware {

    private $_assets;

    private function _mapContentTypeFromExt($ext) {
        if (array_key_exists($ext, direct::$mime)) {
            return direct::$mime[$ext];
        }
        return 'text/plain';
    }

    public function init() {
        $this->_assets = $this->http['assets'];
    }

    public function before() {


        // don't handle this
        if (!isset($this->config['path'])) {
            return;
        }

        // check if we should handle this request
        $path = str_replace('{path}', '(.*)/?', $this->config['path']);
        $matches = [];


        if (!preg_match("#".$path."#i", $this->request->getPathInfo(), $matches)) {
            return;
        }

        // nope
        if (count($matches) == 0){
            return;
        }


        // content
        $content = [];


        $f = $this->_assets->getFilters();

        // explode out the path
        foreach (explode('&', trim($matches[1], '&')) as $path) {
            $info = pathinfo($path);

            if (!$info) {continue;}
            $ext = strtolower($info['extension']);
            $parts = explode("/", trim($path, '/'));


            if ($parts[0] === 'collection') {
                $factory = $this->_assets->factory();

                $a = new AssetCache(
                    $factory->createAsset(
                        "@".str_replace(".{$ext}", '', $parts[1])
                    ),
                    new FilesystemCache('/tmp')
                );

                if (array_key_exists($ext, $f)) {
                    $a = new StringAsset($a->dump(), $f[$ext], $this->_assets->getRoot());
                }


                $content[] = $a->dump();

            }
            else if (($a = $this->_assets->path($path)) != false && file_exists($a)) {
                $content[] = file_get_contents($a);
            }

        }



        // figureo ut
        $this->response->headers->set('Content-Type', $this->_mapContentTypeFromExt($ext));

        // set our content
        $this->response->setContent(implode("",$content));

        // send
        $this->response->isReadyToSend(true);

        return $this->response;

    }

}
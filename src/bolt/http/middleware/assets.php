<?php

namespace bolt\http\middleware;
use \b;

use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;
use Assetic\Asset\GlobAsset;
use Assetic\Asset\StringAsset;

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

        // compiled
        $compiled = [];

        // explode out the path
        foreach (explode('&', trim($matches[1], '&')) as $path) {
            $info = pathinfo($path);

            if (!$info) {continue;}

            $ext = strtolower($info['extension']);

                // get our path
                $dir = $info['dirname'];
                $file = $info['basename'];

                // loop through each path
                if (($file = $this->_assets->find($path)) !== false) {
                    $o = $this->_assets->compileFile($file['path'], $file['rel'], true);

                    $content[] = $o->dump();
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
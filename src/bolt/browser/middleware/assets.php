<?php

namespace bolt\browser\middleware;
use \b;

use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;
use Assetic\Asset\GlobAsset;
use Assetic\Asset\StringAsset;

class assets extends \bolt\browser\middleware {

    private $_assets;

    private function _mapContentTypeFromExt($ext) {
        if (array_key_exists($ext, direct::$mime)) {
            return direct::$mime[$ext];
        }
        return 'text/plain';
    }

    public function init() {
        $this->_assets = $this->browser['assets'];
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
        $compiled = $this->browser->app->getCompiled('assets');

        // explode out the path
        foreach (explode('&', trim($matches[1], '&')) as $path) {
            $info = pathinfo($path);

            if (!$info) {continue;}

            $ext = strtolower($info['extension']);

            if (isset($compiled['data']['files']) AND in_array($path, $compiled['data']['files'])) {
                $content[] = file_get_contents(b::path($compiled['dir'], 'assets', $path));
            }
            else {

                // get our path
                $dir = $info['dirname'];
                $file = $info['basename'];

                // loop through each path
                if (($file = $this->_assets->find($path)) !== false) {
                    $content[] = $this->_assets->compileFile($file['path'], $file['rel'])->dump();
                }

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
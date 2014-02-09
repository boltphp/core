<?php

namespace bolt\browser\middleware;
use \b;

use Symfony\Component\Finder\Finder;


class assets extends \bolt\browser\middleware {

    private $_assets;

    //
    private $_headerMap = [
        'text/css' => ['css','sass','less'],
        'text/javascript' => ['js'],
        'image/png' => ['png'],
        'image/jpeg' => ['jpg','jpeg'],
        'image/gif' => ['gif']
    ];

    private function _mapContentTypeFromExt($ext) {
        foreach ($this->_headerMap as $type => $exts) {
            if (in_array(strtolower($ext), $exts)) {
                return $type;
            }
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

        // explode out the path
        foreach (explode('&', $matches[1]) as $path) {
            $info = pathinfo($path);

            // get our path
            $dir = $info['dirname'];
            $file = $info['basename'];
            $ext = $info['extension'];

            // loop through each path
            if (($file = $this->_assets->find($path)) !== false) {
                $content[] = file_get_contents($file['path']);
            }

        }

        // figureo ut
        $this->response->headers->set('Content-Type', $this->_mapContentTypeFromExt($ext));

        // set our content
        $this->response->setContent(implode("",$content));

        return $this->response;

    }

}
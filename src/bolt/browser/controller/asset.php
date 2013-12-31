<?php

namespace bolt\browser\controller;
use \bolt\browser;
use \b;

use Symfony\Component\Finder\Finder;


class asset extends route {

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

    public function process($file) {
        return $file->getContents();
    }

    public function run($params) {
        $content = "";

        // paths
        $paths = b::settings('browser.paths.assets')->value;

        // explode out the path
        foreach (explode('&', $params['path']) as $path) {
            $info = pathinfo($path);

            // get our path
            $dir = $info['dirname'];
            $file = $info['basename'];
            $ext = $info['extension'];

            // find this template
            $find = new Finder();

            // loop through each path
            foreach ($paths as $path) {

                // find the files
                $files = $find->files()->in(b::path($path, $dir))->name($file);

                if (iterator_count($files)) {
                    $it = iterator_to_array($files);
                    $first = array_shift($it);

                    // process a file and append it's content
                    $content .= $this->process($first);

                }
            }

        }

        // figureo ut
        $this->response->headers->set('Content-Type', $this->_mapContentTypeFromExt($ext));

        $this->response->setContent($content);

    }

}
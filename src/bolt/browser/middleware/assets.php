<?php

namespace bolt\browser\middleware;
use \b;

use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Finder\Finder;


class assets extends \bolt\browser\middleware {

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

    public function handle($req, $res) {
        $route = b::browser('route\create', [
                'path' => $this->config->value('route', '/a/{path}'),
                'require' => ['path' => '.*']
            ]);

        $collection = b::browser('route\collection\create', [$route]);
        $match = new UrlMatcher($collection, $req->getContext());

        // we're going to try and match our request
        // if not we fall back to error
        try {
            $params = $match->matchRequest($req);
        }
        catch(ResourceNotFoundException $e) {
            return false;
        }


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
                    $content .= $this->processFile($first);

                }
            }

        }

        // figureo ut
        $res->headers->set('Content-Type', $this->_mapContentTypeFromExt($ext));

        // set our content
        $res->setContent($content);

        return $res;

    }

}
<?php

namespace bolt\http\assets\filters;
use \b;

use Assetic\Asset\AssetInterface;


class cssRewrite extends \Assetic\Filter\BaseCssFilter {

    private $_http = null;

    public function __construct(\bolt\http $http) {
        $this->_http = $http;
    }

    public function filterLoad(AssetInterface $asset) {

    }

    public function filterDump(AssetInterface $asset) {

        $content = $this->filterReferences($asset->getContent(), function($matches) use ($asset) {
            $url = $matches['url'];

            if (empty($url) || stripos($url, 'http') !== false || substr($url,0,2) === '//' || stripos($url, 'data:') !== false) { return $matches[0]; }

            $root = $asset->getSourceRoot();

            if ($url{0} == '/') {
                $url = $this->_http['assets']->url($url);
            }
            else {
                $path = realpath(b::path($root, $url));
                if ($path AND ($dir = $this->_http['assets']->findDir($path)) !== false) {
                    $url = $this->_http['assets']->url(str_replace($dir, '', $path));
                }
            }

            return str_replace($matches['url'], $url, $matches[0]);
        });

        $asset->setContent($content);

    }

}
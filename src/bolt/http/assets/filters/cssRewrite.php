<?php

namespace bolt\http\assets\filters;
use \b;

use Assetic\Asset\AssetInterface;


class cssRewrite extends \Assetic\Filter\BaseCssFilter {

    private $_assets = null;

    public function __construct(\bolt\http\assets $assets) {
        $this->_assets = $assets;
    }

    public function filterLoad(AssetInterface $asset) {

    }

    public function filterDump(AssetInterface $asset) {

        $content = $this->filterReferences($asset->getContent(), function($matches) use ($asset) {
            $url = $matches['url'];


            if (empty($url) || stripos($url, 'http') !== false || stripos($url, 'data:') !== false) { return $matches[0]; }

            $root = $asset->getSourceRoot();

            if ($url{0} == '/') {
                $url = $this->_assets->url($url);
            }
            else {
                $path = realpath(b::path($root, $url));
                if ($path AND ($dir = $this->_assets->findDir($path)) !== false) {
                    $url = $this->_assets->url(str_replace($dir, '', $path));
                }
            }

            return str_replace($matches['url'], $url, $matches[0]);
        });

        $asset->setContent($content);

    }

}
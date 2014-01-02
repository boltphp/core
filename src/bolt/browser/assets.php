<?php

namespace bolt\browser;
use \b;

use Assetic\FilterManager;
use Assetic\AssetManager;
use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;
use Assetic\Asset\GlobAsset;
use Assetic\Asset\HttpAsset;
use Assetic\Asset\StringAsset;

// filters
use Assetic\Filter\CssRewriteFilter;


class assets implements \bolt\plugin\singleton {
    use \bolt\plugin\singletonTraits;

    private $_manager = false;
    private $_paths = [];
    private $_filters = ['*' => []];


    public function __construct() {

        // manager
        $this->_manager = new AssetManager();

    }

    public function filter($ext, $filter=null, $useInDev=true) {
        if (is_array($ext)) {
            array_map(function(){ call_user_func_array([$this, 'filter'], func_get_args()); }, $ext);
            return $this;
        }
        $class = "\\Assetic\\Filter\\{$filter}Filter";

        if (!array_key_exists($ext, $this->_filters)) {
            $this->_filters[$ext] = [];
        }

        if (class_exists($class, true)) {
            $this->_filters[$ext][] = [$class, $useInDev];
        }
    }

    public function getFilters() {
        return $this->_filters;
    }

    public function stylesheet($leafs) {
        if (is_string($leafs)) { $leafs = [$leafs]; }

        $output = [];

        foreach ($leafs as $leaf) {
            $output[] = $leaf;
        }

        return implode("\n", array_map(function($href) {
            return '<link rel="stylesheet" href="'.$href.'" type="text/css">';
        }, $output));

    }

    public function addPaths($paths=[]) {
        $this->_paths = array_replace($this->_paths, $paths);
        return $this;
    }

    public function find($find, $root=false) {
        $this->addPaths(b::settings()->value("browser.paths.assets", []));
        foreach (array_merge([$root], $this->_paths) as $path) {
            $_ = b::path($path, $find);
            if (file_exists($_)) {
                return $_;
            }
        }
        return false;
    }

    public function getFile($path) {
        $f =  new FileAsset($path);
        $f->load();
        return $f;
    }

    public function processFile($path, $config=[]) {
        $rel = b::param('rel', false, $config);
        $url = b::param('url', false, $config);
        $useGlobalFilters = b::param('useGlobalFilters', true, $config);

        // get the file
        $file = $this->getFile($path);

        $root = pathinfo($path)['dirname'];
        $ext = pathinfo($path)['extension'];

        // get it's tree
        $content = $file->getContent();

        if (empty($content)) { return $content; }

        // parse the string
        $found = $this->parseString($content, $root);

        $tree = $this->getCombinedTree($found, $ext);

        $reduce = function($items, $reduce) {
            $resp = [];
            foreach ($items as $key => $files) {
                $resp[] = $key;
                $resp += $reduce($files, $reduce);
            }
            return $resp;
        };

        // loop through each file and append
        foreach (array_unique($reduce($tree, $reduce)) as $f) {
            if ($f === $path) {continue;}
            $content .= $this->processFile($f);
        }

        $source = false;
        $sourcePath = false;
        $targetPath = false;

        if ($url) {
            $parts = parse_url($url);
            $source = "{$parts['scheme']}://{$parts['host']}";
            $targetPath = trim($parts['path'], '/');
            $sourcePath = $targetPath .'/'.trim($rel,'/');
        }

        $a = new StringAsset($content, [], $source, $sourcePath);

        if ($targetPath) {
            $a->setTargetPath($targetPath);
        }

        // use filters
        if ($useGlobalFilters !== false) {
            if (array_key_exists($ext, $this->_filters)) {
                foreach ($this->_filters[$ext] as $filter) {
                    if ($filter[1] === false AND b::env() === 'dev') {continue;}
                    $a->ensureFilter(new $filter[0]());
                }
            }
            foreach ($this->_filters['*'] as $filter) {
                if ($filter[1] === false AND b::env() === 'dev') {continue;}
                $a->ensureFilter(new $filter[0]());
            }
        }

        $a->ensureFilter(new CssRewriteFilter());


        return trim($a->dump());
    }

    public function parseString($str, $root=false) {

        $find = [
            'file' => '#\$file ([^\n]+)#',
            'glob' => '#\$glob ([^\n]+)#',
            'dir' => '#\$dir ([^\n]+)#',
            'filter' => '#\$filter ([^\n]+)#'
        ];

        $found = [];

        // parse the string
        foreach ($find as $type => $pat) {
            $found[$type] = [];
            if (preg_match_all($pat, $str, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $found[$type] += array_map(function($item){
                        return $item;
                    }, explode(' ', trim($match[1], '')));
                }
            }
        }

        // make full paths
        foreach (['file', 'dir'] as $type) {
            foreach ($found[$type] as $i => $path) {
                if (($file = $this->find($path, $root)) != false) {
                    $found[$type][$i] = $file;
                }
                else {
                    unset($found[$type][$i]);
                }
            }
        }

        // make sure we have a valid filter
        array_filter($found['filter'], function($name){
            return class_exists("\\Assetic\\Filter\\{$name}Filter", true);
        });

        return $found;
    }

    public function getCombinedTree($found, $ext) {
        $tree = [];

        // file
        if (isset($found['file'])) {
            foreach ($found['file'] as $path) {
                $file = $this->getFile($path);
                $tree[$path] = $this->processFileTree($file, $ext);
            }
        }

        if (isset($found['dir'])) {
            foreach ($found['dir'] as $path) {
                $tree += $this->processDirTree($path, $ext);
            }
        }

        return $tree;
    }

    public function processFileTree($file, $ext) {
        $str = $file->getContent();

        // parse the str
        $found = $this->parseString($str);

        return $this->getCombinedTree($found, $ext);

    }

    public function processDirTree($path, $ext) {
        $files = b::getRegexFiles($path, "^.+\.{$ext}$");
        $tree = [];
        foreach ($files as $file) {
            $tree[$file] = $this->processFileTree($this->getFile($file), $ext);
        }
        return $tree;
    }

}
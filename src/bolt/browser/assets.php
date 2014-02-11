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
use Assetic\Filter\CssRewriteFilter;


/**
 * asset manager
 */
class assets implements \bolt\plugin\singleton {

    /**
     * @var bolt\browser
     */
    private $_browser;

    /**
     * @var array
     */
    private $_config = [];

    /**
     * @var array
     */
    private $_dirs = [];

    /**
     * @var array
     */
    private $_filters = [];

    /**
     * @var array
     */
    private $_globals = [];

    /**
     * @var array
     */
    private $_output = [
        'script' => [],
        'style' => []
    ];


    /**
     * Constructor
     *
     * @param bolt\browser $browser
     * @param array $config
     */
    public function __construct(\bolt\browser $browser, $config=[]) {
        $this->_browser = $browser;
        $this->_config = $config;
        $this->_dirs = isset($config['dirs']) ? $config['dirs'] : [];

        if (isset($config['path'])) {
            $browser->bind('assets', 'bolt\browser\middleware\assets', $config);
        }
        if (isset($config['filters'])) {
            $this->filter($config['filters']);
        }
        if (isset($config['globals'])) {
            $this->globals($config['globals']);
        }
    }


    /**
     * register files to append to specific file type (by file extension)
     *
     * @param string|array $ext string of file exenstion or array of globals
     * @param string $path
     *
     * @return self
     */
    public function globals($ext, $path = false) {
        if (is_array($ext)) {
            foreach ($ext as $item) {
                call_user_func_array([$this, 'globals'], $item);
            }
            return $this;
        }
        if (!array_key_exists($ext, $this->_globals)) { $this->_globals[$ext] = []; }
        $this->_globals[$ext][] = $this->_browser->path($path);
        return $this;
    }


    /**
     * register a filter for a file type (by file extension)
     *
     * @param string|array $ext string of file extension or array of filters
     * @param string $class
     * @param array $args array of args passed to filter constuctor (via call_user_func_array)
     *
     * @return self
     */
    public function filter($ext, $class=false, $args = []) {
        if (is_array($ext)) {
            foreach ($ext as $item) {
                call_user_func_array([$this, 'filter'], $item);
            }
            return $this;
        }

        if (!array_key_exists($ext, $this->_filters)) { $this->_filters[$ext] = []; }
        $this->_filters[$ext][] = [
            'class' => $class,
            'instance' => false,
            'args' => $args,
            'ref' => b::getReflectionClass($class)
        ];
        return $this;
    }


    /**
     * add a new file to the manager
     *
     * @param string|array $type file type or array of files
     * @param array $config
     *
     * @return mixed
     */
    public function add($type, $config=false) {
        if (is_array($type) AND !$config) {
            foreach ($type as $item) {
                $this->add($item[0], $item[1]);
            }
            return $this;
        }

        $o = $type == 'script' ? new assets\script($this) : new assets\style($this);

        if (is_string($config)) {
            $o->setPath($config);
        }
        else if (is_array($config)) {
            foreach ($config as $k => $v) {
                call_user_func([$o, "set{$k}"], $v);
            }
        }
        $this->_output[$type][] = $o;
        return $o;
    }


    /**
     * find a file in one of $dirs
     *
     * @param string $file relative file path
     *
     * @return mixed
     */
    public function find($file) {

        foreach ($this->_dirs as $dir) {
            $path = $this->_browser->path($dir, $file);
            if (file_exists($path)) {
                return [
                    'path' => $path,
                    'rel' => $this->_browser->path($dir)
                ];
            }
        }

        return false;
    }


    /**
     * output a tag for give group
     *
     * @param string $group
     * @param string $type
     *
     * @return string
     */
    public function out($group, $type=false) {
        $tag = [];

        if (!$type OR $type == 'script') {
            foreach ($this->_output['script'] as $script) {
                if ($script->inGroup($group)) {
                    $tag[] = $script->out();
                }
            }
        }

        if (!$type OR $type == 'style') {
            foreach ($this->_output['style'] as $leaf) {
                if ($leaf->inGroup($group)) {
                    $tag[] = $leaf->out();
                }
            }
        }

        return implode("\n", $tag);

    }


    /**
     * url path
     *
     * @param string $path
     *
     * @return string
     */
    public function url($path) {
        return str_replace('{path}', "{$path}", rtrim($this->_config['path'],'/'));
    }


    /**
     * get all file objects in a group
     *
     * @param string $type
     * @param string $group
     *
     * @return array
     */
    public function getByGroup($type, $group) {
        $items = [];
        foreach ($this->_output[$type] as $item) {
            if ($item->inGroup($group)) {
                $items[] = $item;
            }
        }
        return $items;
    }


    /**
     * return a stylesheet tag
     *
     * @param array $leafs
     *
     * @return string
     */
    public function stylesheet($leafs) {
        if (is_string($leafs)) { $leafs = [$leafs]; }

        $output = [];

        foreach ($leafs as $leaf) {
            $output[] = str_replace('{path}', "{$leaf}", rtrim($this->_config['path'],'/'));
        }

        return implode("\n", array_map(function($href) {
            return '<link rel="stylesheet" href="'.$href.'" type="text/css">';
        }, $output));

    }


    /**
     * get all registered globals for an extension
     *
     * @param string $ext
     *
     * @return array
     */
    public function getGlobals($ext) {
        return array_key_exists($ext, $this->_globals) ? $this->_globals[$ext] : [];
    }


    /**
     * get all registered filters for an extension
     *
     * @param string $ext
     *
     * @return array
     */
    public function getFilters($ext) {
        $items = [];
        if (array_key_exists($ext, $this->_filters)) {
            foreach ($this->_filters[$ext] as $i => $filter) {
                if (!$filter['instance']) {
                    $this->_filters[$ext][$i]['instance'] = $filter['instance'] = $filter['ref']->newInstanceArgs($filter['args']);
                }
                $items[] = $filter['instance'];
            }
        }
        return $items;
    }

}
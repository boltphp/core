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
    private $_groups = [];


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
     * @param string $type script or style
     * @param string $name name of group
     * @param array $files array of files
     *
     * @return self
     */
    public function add($type, $name = false, $files = false) {
        if (is_array($type)) {
            foreach ($type as $item) {
                call_user_func_array([$this, 'add'], $item);
            }
            return $this;
        }

        $_ = $this->_groupName($type, $name);

        if (!array_key_exists($_, $this->_groups)) {
            $this->_groups[$_] = new assets\group($this, $name, $type);
        }

        // add some files
        $this->_groups[$_]->add($files);

        return $this;
    }

    private function _groupName($type, $name) {
        return implode("_", [$type, $name]);
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
    public function out($name, $type=false) {
        $tag = [];

        foreach ($this->_groups as $group) {
            if ($group['name'] != $name OR ($type AND $type !== $group['type'])) {continue;}

            var_dump($group); die;
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
    public function getGroup($name, $type) {
        $_ = $this->_groupName($type, $name);
        return array_key_exists($_, $this->_groups) ? $this->_groups[$_] : null;
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
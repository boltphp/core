<?php

namespace bolt\browser;

use \Exception;


/**
 * views manager
 */
class views {

    /**
     * @var array
     */
    private $_views = [];

    /**
     * @var array
     */
    private $_layouts = [];


    /**
     * @var array
     */
    private $_engines = [];

    /**
     * @var string
     */
    private $_class = 'bolt\browser\views\view';


    /**
     * Constructor
     *
     * @param bolt\browser $browser
     * @param array $config
     */
    public function __construct(\bolt\browser $browser, $config = []) {

        $this->_browser = $browser;

        $this->_views = isset($config['views']) ? (array)$config['views'] : [];
        $this->_layouts = isset($config['layouts']) ? (array)$config['layouts'] : [];

        if (isset($config['engines'])) {
            foreach ($config['engines'] as $engine) {
                $this->engine($engine[0], $engine[1]);
            }
        }

    }


    /**
     * add a dir
     *
     * @param string $path
     * @param string $type type of dir to add
     *
     * @return self
     */
    public function dir($path, $type = 'views') {
        if (is_array($path)) {
            foreach ($path as $item) {
                $this->dir($item, $type);
            }
            return $this;
        }
        $type == 'layouts' ? $this->_layouts[] = $path : $this->_views[] = $path;
        return $this;
    }


    /**
     * get view directories
     *
     * @return array
     */
    public function getViewDirs() {
        return $this->_views;
    }

    /**
     * get layout directories
     *
     * @return array
     */
    public function getLayoutDirs() {
        return $this->_layouts;
    }


    /**
     * register a new engine
     *
     * @param string $ext
     * @param string $class
     *
     * @return self
     */
    public function engine($ext, $class) {
        $this->_engines[$ext] = [
            'class' => $class,
            'instance' => false
        ];
        return $this;
    }


    /**
     * get all register engiens
     *
     * @return array
     */
    public function getEngines() {
        return $this->_engines;
    }


    /**
     * find a view file in given dirs
     *
     * @param string $file
     * @param array $dirs
     *
     * @return string
     */
    public function find($file, array $dirs) {
        foreach ($dirs as $dir) {
            $_ = $this->_browser->path($dir, $file);
            if (file_exists($_)){
                return $_;
            }
        }
        return false;
    }


    /**
     * check if a view exists
     *
     * @param string $file
     * @param array $dirs
     *
     * @return bool
     */
    public function exists($file, array $dirs = null) {
        return $this->find($file, $dirs) !== false;
    }

    /**
     * return a view of self::$class for given file
     *
     * @param string $file relative file path to $_dirs
     * @param array $var
     * @param mixed $context
     *
     * @return self::$_class
     */
    public function view($file, $vars = [], $context = false) {
        return $this->create(
                        $this->find($file, $this->_views),
                        $vars,
                        $context
                    );
    }


    /**
     * return a view of self::$class for layout file given
     *
     * @param string $file relative file path to $_layouts
     * @param array $vars
     * @param mixed $context
     *
     * @return self::$_class
     */
    public function layout($file, $vars = [], $context = false) {
        return $this->create(
                $this->find($file, $this->_layouts),
                $vars,
                $context
            );
    }


    /**
     * create a view of self::$_class
     *
     * @param string $file absolute file path
     * @param array $vars
     * @param object $context
     *
     * @return self::$_class
     */
    public function create($file, $vars = [], $context = false) {

        if (!$file) {
            throw new Exception("Unable to find view '$file'.");
        }

        // ext
        $ext = strtolower(pathinfo($file)['extension']);

        // need an engine
        if (!array_key_exists($ext, $this->_engines)) {
            throw new Exception("Unable to find render engine for '$ext'.");
        }

        $engine = $this->_engines[$ext];

        // no instance
        if (!$engine['instance']) {
            $engine['instance'] = $this->_engines[$ext]['instance'] = new $engine['class'];
        }

        // create our view
        return new $this->_class($this, $file, $engine['instance'], ['vars' => $vars, 'context' => $context ]);

    }

}
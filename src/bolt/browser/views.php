<?php

namespace bolt\browser;
use \b;

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

        // make sure we have a render plugin
        if (!$browser->app->pluginExists('render')) {
            $browser->app->plug('render', '\bolt\render');
        }

        if (isset($config['engines'])) {
            foreach ($config['engines'] as $engine) {
                $browser->app['render']->engine($engine[0], $engine[1]);
            }
        }

        // when compile
        $browser->app->on('compile', [$this, 'compile']);

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


    public function engine($ext, $class) {
        $this->_browser->app['render']->engine($ext, $class);
        return $this;
    }

    public function getEngines() {
        return $this->_browser->app['render']->getEngines();
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
     * find a view file in given dirs
     *
     * @param string $file
     * @param array $dirs
     *
     * @return string
     */
    public function find($file, array $dirs = null) {
        $dirs == null ? $dirs = $this->_views : [];

        $compiled = $this->_browser->app->getCompiled('views');


        foreach ($dirs as $dir) {
            $rel = b::path($dir, $file);
            if (isset($compiled['data']['map']) AND array_key_exists($rel, $compiled['data']['map'])) {
                $compiled['data']['map'][$rel]['content'] = require("{$compiled['dir']}/views/{$compiled['data']['map'][$rel]['name']}");
                return $compiled['data']['map'][$rel];
            }
            $_ = $this->_browser->path($rel);
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
        $ext = is_array($file) ? $file['ext'] : strtolower(pathinfo($file)['extension']);

        // make sure we have an engine for this ext
        if (!$this->_browser->app['render']->hasEngine($ext)) {
            throw new Exception("Unable to find render engine for '$ext'.");
        }

        // create our view
        return new $this->_class($this, $file, ['vars' => $vars, 'context' => $context ]);

    }


    public function renderFile($file, $vars = []) {
        return $this->_browser->app['render']->file($file, $vars);
    }

    public function renderString($str, $vars = []) {
        return $this->_browser->app['render']->string($str, $vars);
    }

    public function hasEngine($ext) {
        return $this->_browser->app['render']->hasEngine($ext);
    }

    public function getEngine($ext, $mustCompile = false) {
        return $this->_browser->app['render']->getEngine($ext, $mustCompile);
    }

    public function compile($e) {
        $vdir = $e->data['client']->makeDir("views");

        $map = [];

        $dirs = [];

        // loop through all directories and find
        // files that we can compile
        foreach ($this->_views as $dir) {
            $root = $this->_browser->path($dir);
            $dirs[$dir] = array_merge(b::fs('glob', $root."/**/*.*")->asArray(), b::fs('glob', $root."/*.*")->asArray() );
        }

        foreach ($dirs as $root => $files) {
            foreach ($files as $file) {
                $ext = pathinfo($file)['extension'];
                $rel = str_replace($this->_browser->app->getRoot(), '', $file);
                if ($this->hasEngine($ext, true)) {
                    $id = md5($rel);
                    $map[$rel] = [
                        'modified' => filemtime($file),
                        'dir' => $dir,
                        'name' => "{$id}.php",
                        'ext' => $ext
                    ];
                    $var = $this->getEngine($ext)->compile( file_get_contents($file));
                    file_put_contents("{$vdir}/{$id}.php", '<?php return '.var_export($var, true).';');
                }
            }
        }


        $e->data['client']->saveCompileLoader('views', ['map' => $map]);


    }

}
<?php

namespace bolt\http;
use \b;

/**
 * base controller class
 */
class controller {
    use \bolt\helpers\events;

    /**
     * @var string
     */
    protected $layout = null;

    /**
     * @var bool
     */
    protected $_useLayout = null;

    /**
     * @var array
     */
    private $_parameters = [];

    /**
     * @var bolt\http
     */
    protected $_http;

    /**
     * @var bolt\application
     */
    protected $_app;


    /**
     * Construct
     *
     * @param bolt\http
     *
     */
    public function __construct(\bolt\http $http) {

        $this->_http = $http;
        $this->_app = $http->app;

        $this->init();

    }


    /**
     * initalize class holder class
     */
    public function init() {}

    public function before() {}

    public function after() {}


    /**
     * toggle use the layout
     *
     * @param bool $layout
     *
     * @return self
     */
    public function useLayout($layout) {
        $this->_useLayout = $layout;
        return $this;
    }

    /**
     * check useLayout varaible
     */
    public function getUseLayout() {
        if ($this->_useLayout === null) {
            $this->_useLayout = $this->request->getRequestFormat() === 'html';
        }
        return $this->_useLayout;
    }

    /**
     * get a magic variables
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name) {
        if (array_key_exists($name, $this->_parameters)) {
            return $this->_parameters[$name];
        }

        // fallback
        switch($name) {
            case 'app':
                return $this->_app;
            case 'http':
                return $this->_http;
        }

        // http plugin exists
        if ($this->_http->pluginExists($name)) {
            return $this->_http->plugin($name);
        }

        // app plugin exists
        if ($this->_http->app->pluginExists($name)) {
            return $this->_http->app->plugin($name);
        }

        return null;
    }

    /**
     * set a paramater
     *
     * @param string $name name of paramater
     * @param mixed $value value of paramater
     *
     * @return self
     */
    public function __set($name, $value) {
        $this->_parameters[$name] = $value;
        return $this;
    }

    /**
     * does a param exist
     *
     * @param $name $name
     *
     * @return bool
     */
    public function __isset($name) {
        return array_key_exists($name, $this->_parameters);
    }


    /**
     * get paramaters
     *
     * @return array
     */
    public function getParameters() {
        return $this->_parameters;
    }


    /**
     * create a view
     *
     * @param string $file file name
     * @param array $vars
     *
     * @return mixed (view object)
     */
    public function view($file, $vars=[]) {
        if (!$this->http['views']) {
            throw new \Exception('No view manager');
        }

        return $this->http['views']->create($file, $this->getViewVars($vars), $this);
    }


    /**
     * get a combined view vars
     *
     * @param array $vars
     *
     * @return array
     */
    protected function getViewVars(array $vars) {
        foreach ($this->_parameters as $key => $value) {
            if (!array_key_exists($key, $vars)) {
                $vars[$key] = $value;
            }
        }
        return $vars;
    }

    /**
     * return a list of arguments matched
     * against a method refactor class
     *
     * @param object $ref method refrance
     * @param array $params array of paramaters
     * @param array $classes array of class maps
     *
     * @return array
     */
    protected function getArgsFromMethodRef($ref, $params, $classes = []) {

        // must be a subclass of ReflectionFunctionAbstract
        if (!is_subclass_of($ref, 'ReflectionFunctionAbstract')) {
            throw new \Exception('Class must be an implementation of "ReflectionFunctionAbstract"');
        }

        // we need to get all args
        // from the function and see what we can prefill
        $args = [];

        foreach ($ref->getParameters() as $param) {

            if ($param->getClass() && array_key_exists($param->getClass()->name, $classes)) {
                $args[] = $classes[$param->getClass()->name];
            }
            else if (array_key_exists($param->getName(), $params)) {
                $args[] = $params[$param->getName()];
            }
            else if ($param->isOptional()) {
                $args[] = $param->getDefaultValue();
            }
        }

        return $args;
    }

}
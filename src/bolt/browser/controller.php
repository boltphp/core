<?php

namespace bolt\browser;
use \b;

/**
 * base controller class
 */
class controller {

    /**
     * @var string
     */
    protected $layout = null;

    /**
     * @var bool
     */
    protected $_useLayout = true;

    /**
     * @var array
     */
    private $_parameters = [];

    /**
     * @var bolt\browser
     */
    private $_browser;


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
        if (!$this->browser['views']) {
            throw new \Exception('No view manager');
            return;
        }
        return $this->browser['views']->view($file, $vars, $this);
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
            return false;
        }

        // we need to get all args
        // from the function and see what we can prefill
        $args = [];

        foreach ($ref->getParameters() as $param) {

            if ($param->getClass() AND array_key_exists($param->getClass()->name, $classes)) {
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
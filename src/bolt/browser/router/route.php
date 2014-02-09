<?php

namespace bolt\browser\router;
use \b;

use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route as sRoute;


/**
 * base route class
 */
class route extends sRoute implements face {

    /**
     * @var string
     */
    private $_name = null;

    /**
     * static create a route class
     *
     * @param array $route
     *
     * @return bolt\browser\router\route
     */
    public static function create($route) {
        $path = $route['path'];
        $r = new route($path);
        foreach ($route as $name => $value) {
            if (method_exists($r, "set{$name}")) {
                $r->{"set{$name}"}($value);
            }
        }
        return $r;
    }


    /**
     * set the route name
     *
     * @param string $name
     *
     * @return self
     */
    public function setName($name) {
        $this->_name = $name;
        return $this;
    }


    /**
     * get the route name
     *
     * @return string
     */
    public function getName() {
        if ($this->_name === null) { $this->setName(b::guid('route')); }
        return $this->_name;
    }


    /**
     * set the route controller
     *
     * @param mixed $controller
     *
     * @return self
     */
    public function setController($controller) {
        if (is_a($controller, 'Closure')) {
            $this->addDefaults(['_closure' => $controller]);
            $controller = '\bolt\browser\controller\closure';
        }
        $this->addDefaults(['_controller' => $controller]);
        return $this;
    }


    /**
     * set a required param
     *
     * @param string $require
     *
     * @return self
     */
    public function setRequire($require) {
        $this->addRequirements($require);
        return $this;
    }


    /**
     * set the controller action
     *
     * @param string $action
     *
     * @return self
     */
    public function setAction($action) {
        $this->addDefaults(['_action' => $action]);
        return $this;
    }


    /**
     * set response formats
     *
     * @param string $format
     *
     * @return self
     */
    public function setFormats($format) {
        $default = $this->getDefaults();
        $formats = array_merge(
            (array_key_exists('_formats', $default) ? $default['formats'] : []),
            (is_array($format) ? $format : explode(',', $format))
        );

        // optional
        if (isset($formats[0]) AND $formats[0]{0} === '?') {
            $formats[0] = substr($formats[0],1);
            $this->addDefaults(['_format' => $formats[0]]);
        }

        $this->addDefaults(['_formats' => $formats]);
        return $this;
    }


    /**
     * compile the route
     *
     * @return mixed
     */
    public function compile() {
        $defaults = $this->getDefaults();

        if (array_key_exists('_formats', $defaults)) {
            $path = $this->getPath();

            // add format to the path
            $this->setPath($path.".{_format}");

            // add a requirement
            $this->addRequirements(['_format' => implode('|', $defaults['_formats'])]);

        }

        return parent::compile();
    }

}
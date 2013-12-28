<?php

namespace bolt\browser;
use \b;

use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route as sRoute;


class route extends sRoute {

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

    public function setController($controller) {
        if (is_a($controller, 'Closure')) {
            $this->addDefaults(['_closure' => $controller]);
            $controller = '\bolt\browser\controller\closure';
        }
        $this->addDefaults(['_controller' => $controller]);
        return $this;
    }

    public function setAction($action) {
        $this->addDefaults(['_action' => $action]);
        return $this;
    }

    public function setFormats($format) {
        $default = $this->getDefaults();
        $formats = array_merge(
            (array_key_exists('_formats', $default) ? $default['formats'] : []),
            (is_array($format) ? $format : explode(',', $format))
        );
        $this->addDefaults(['_formats' => $formats]);
        return $this;
    }

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
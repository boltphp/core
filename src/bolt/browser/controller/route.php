<?php

namespace bolt\browser\controller;
use \bolt\browser;
use \b;


class route extends browser\controller implements browser\route\face {


    private $_formats = [];

    protected $response = false;
    protected $request = false;

    final public function __construct($req, $res) {

        $this->request = $req;
        $this->response = $res;

        $this->init();

    }

    public function __get($name) {

        return false;
    }

    public function format($format, $content=false) {
        if (is_array($format)) {
            array_walk($format, function($content, $format){
                $this->format($format, $content);
            });
            return $this;
        }
        $class = 'bolt\browser\response\format\\'.$format;
        $this->_formats[$format] = new $class($this->response);
        $this->_formats[$format]->setContent($content);
        return $this;
    }


    public function build($params=[]) {

        // what method
        $method = $this->request->getMethod();

        // is there an action
        $action = b::param('_action', false, $params);

        // lets figure out what function we're going to call
        $try = [
            'dispatch',
            "{$method}{$action}",
            $action,
            $method
        ];

        foreach ($try as $func) {
            if (method_exists($this, $func)) {
                break;
            }
        }

        // reflect the controller and method
        // and figure out what params we need
        $ref = b::getReflectionClass(get_called_class());

        // get
        $args = $this->getArgsFromMethodRef($ref->getMethod($func), $params);

        // wonderfull, lets call the function and figure out what
        // they respond with
        $resp = call_user_func_array([$this, $func], $args);

    }

    public function run($params) {

        $resp = $this->build($params);


        if (is_string($resp)) {
            $this->response->setContent($resp);
            $resp = false;
        }

        // if resp is a request
        // we can reset our request and be done
        if (is_array($resp))  {
            $this->format($resp);
        }

            // if _format exists in response. no we return
        if (array_key_exists('_format', $params) AND array_key_exists($params['_format'], $this->_formats)) {
            $resp = $this->_formats[$params['_format']];
        }
        else if (array_key_exists('_format', $params)) {
            throw new \ResourceNotFoundException("Unable to match response", 404);
        }

        if (is_callable($resp)) {
            $resp = call_user_func($resp);
        }

        if (!$resp) {
            $resp = $this->response;
        }

        return $resp;
    }

}
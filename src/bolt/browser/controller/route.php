<?php

namespace bolt\browser\controller;
use \bolt\browser;
use \b;


use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class route extends browser\controller implements browser\route\face {


    private $_formats = [];

    protected $response = false;
    protected $request = false;

    final public function __construct($req, $res) {

        $this->request = $req;
        $this->response = $res;

        $this->init();

    }

    public function exception($class, $message=null, $code=null) {
        switch($class) {
            case 'MethodNotAllowedException':
                $allowed = array_filter(['get', 'put', 'post', 'delete'], function($method) { return method_exists($this, $method); });
                throw new MethodNotAllowedException($allowed, $message, $code);
                break;
            default:
                throw new $class($message, $code);
        };
        return $this;
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
        return call_user_func_array([$this, $func], $args);

    }

    public function run($params) {

        // resp
        $resp = $this->build($params);

        // if it's an array,
        // we assume they have given formats
        if (is_string($resp)) {
            $this->response->setContent($resp);
            $resp = false; // fallback to default response
        }
        else if ($resp instanceof \bolt\browser\view) {
            $this->response->setContent($resp->render());
            $resp = false; // fallback to default response
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

        // if we have a layout, we need to
        // wrap our current content in that layout
        if ($this->layout !== null AND $resp->useLayout() === true) {
            $resp->setContent(
                $this->view(
                    $this->layout,
                    ['yeild' => $resp->getContent()],
                    false
                )
            );
        }

        return $resp;
    }

}
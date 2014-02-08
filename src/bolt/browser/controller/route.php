<?php

namespace bolt\browser\controller;
use \bolt\browser;
use \b;


use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class route extends browser\controller implements browser\router\face {

    private $_app;
    private $_browser;

    private $_formats = [];

    protected $_response = false;
    protected $_request = false;

    final public function __construct(\bolt\application $app, \bolt\browser $browser) {

        $this->_app = $app;
        $this->_browser = $browser;

        $this->init();

    }

    public function __get($name) {
        switch($name) {
            case 'app':
                return $this->_app;
            case 'browser':
                return $this->_browser;
            case 'request':
                return $this->_browser->getRequest();
            case 'response':
                return $this->_browser->getResponse();

        };

        return false;
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
        return $this->_formats[$format];
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

        // if resp is a request
        // we can reset our request and be done
        if (is_array($resp))  {
            $this->format($resp);
        }

        // if it's an array,
        // we assume they have given formats
        if (is_string($resp)) {
            $this->response->setContent($resp);
        }
        else if ($resp instanceof \bolt\browser\view) {
            $content = $resp->render();
        }
        else if (is_a($resp, 'bolt\browser\response') AND $resp !== $this->response) {
            return $resp;
        }

        // if the build function set content and
        // we don't have any formats set
        // assume they set the default format
        if ($this->response->getContent() !== "" AND count($this->_formats) === 0) {
            $this->_formats[$params["_format"]] = $this->response->getContent();
        }


        // our default content
        $content = "";

        // if _format exists in response. no we return
        if (array_key_exists('_format', $params) AND array_key_exists($params['_format'], $this->_formats)) {
            $content = $this->_formats[$params['_format']];
        }
        else if (array_key_exists('_format', $params)) {
            throw new \ResourceNotFoundException("Unable to match response", 404);
            return;
        }

        // if our content is callable
        // we want to do that now
        while(is_callable($content)) {
            $content = call_user_func($content);
        }

        // set our content in the response
        $this->response->setContent($content);

        return $this->response;
    }

}
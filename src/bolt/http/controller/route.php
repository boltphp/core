<?php

namespace bolt\http\controller;
use \bolt\http;
use \b;

use Symfony\Component\Routing\Exception\MethodNotAllowedException;

/**
 * route controller
 */
class route extends http\controller implements http\router\face {

    /**
     * @var bolt\http\response\format[]
     */
    private $_formats = [];

    /**
     * @var bolt\http\response
     */
    protected $_response = false;

    /**
     * @var bolt\http\request
     */
    protected $_request = false;

    /**
     * Construct
     *
     * @param bolt\http
     *
     */
    final public function __construct(\bolt\http $http, \bolt\http\request $req = null, \bolt\http\response $resp = null) {
        parent::__construct($http);

        $this->_request = $req ?: $http->getRequest();
        $this->_response = $resp ?: $http->getResponse();

    }


    /**
     * magic get method
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name) {
        switch($name) {
            case 'request':
                return $this->_request;
            case 'response':
                return $this->_response;
        };

        return parent::__get($name);
    }

    public function setResponse(\bolt\http\response $resp) {
        $this->_response = $resp;
        return $this;
    }

    /**
     * throw an exception
     *
     * @param string $class
     * @param string $message
     * @param int $code
     *
     * @return self
     */
    public function exception($class, $message=null, $code=null) {
        switch($class) {
            case 'MethodNotAllowedException':
                $allowed = array_filter(['get', 'put', 'post', 'delete'], function($method) { return method_exists($this, $method); });
                throw new MethodNotAllowedException($allowed, $message, $code);
            default:
                throw new $class($message, $code);
        };
        return $this;
    }


    /**
     * add a response format
     *
     * @param string|array $format format name or array of formats
     * @param mixed $content
     *
     * @return bolt\http\response\format
     */
    public function format($format, $content=false) {
        if (is_array($format)) {
            array_walk($format, function($content, $format){
                $this->format($format, $content);
            });
            return $this;
        }

        $class = $format;

        if (!class_exists($class, true)) {
            $class = 'bolt\http\response\format\\'.$format;
        }

        if (!class_exists($class, true)) {
            throw new \Exception("Unknown format class $class");
        }

        $o = new $class($this->response);


        if (!is_subclass_of($o, 'bolt\http\response\format\face')) {
            throw new \Exception('Format class does not implement bolt\http\response\format\face');
        }

        $this->_formats[$format] = $o;
        $this->_formats[$format]->setContent($content);
        return $this->_formats[$format];
    }


    /**
     * build the controller
     *
     * @param array $params
     *
     * @return mixed
     */
    public function build($params=[]) {

        $this->fire('beforeBuild');

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

        // things we want to map
        $map = [
            'bolt\http\request' => $this->request,
            'bolt\http\response' => $this->response
        ];

        // get
        $args = $this->getArgsFromMethodRef($ref->getMethod($func), $params, $map);

        // wonderfull, lets call the function and figure out what
        // they respond with
        return call_user_func_array([$this, $func], $args);

    }


    /**
     * run the controller
     *
     * @param array $params
     *
     * @return bolt\http\response
     */
    public function run($params) {


        $this->fire('beforeRun');


        // resp
        try {
            // run before
            $this->before();

            // build
            $resp = $this->build($params);

            // after
            $this->after();

        }
        catch (\Exception $e) {
            $this->response->setException($e);
            return $this->response;
        }


        // if resp is a request
        // we can reset our request and be done
        if (is_array($resp))  {
            $this->format($resp);
        }

        $content = $this->response->getContent();

        // if it's an array,
        // we assume they have given formats
        if (is_string($resp)) {
            $this->response->setContent($resp);
        }
        else if ($resp instanceof \bolt\http\views\face) {
            $content = $resp->render();
        }
        else if (is_a($resp, 'bolt\http\response') && $resp !== $this->response) {
            return $resp;
        }

        // if the build function set content and
        // we don't have any formats set
        // assume they set the default format
        if ($content !== "" && count($this->_formats) !== 0 && isset($params["_format"])) {
            $this->_formats[$params["_format"]] = $this->response->getContent();
        }

        // if _format exists in response. no we return
        if (array_key_exists('_format', $params) && array_key_exists($params['_format'], $this->_formats)) {
            $content = $this->_formats[$params['_format']];
        }
        else if (array_key_exists('_format', $params)) {
            $this->response->setException(new \Exception("Unable to match response", 404));
            return $this->response;
        }
        else if (count($this->_formats) == 1) {
            $content = array_shift($this->_formats);
        }

        // if our content is callable
        // we want to do that now
        while(is_callable($content)) {
            $content = call_user_func($content);
        }

        // use the layout
        if ($this->getUseLayout() && $this->layout) {
            $content = $this->view($this->layout, ['yield' => $content], $this)->render();
        }

        // set our content in the response
        $this->response->setContent($content);

        $this->fire('afterRun');

        return $this->response;
    }

}
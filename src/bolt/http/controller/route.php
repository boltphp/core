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
        return $this->_response->format($format, $content, $this);
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
        $resp = false;

        // before we do anything
        $this->fire('beforeRun');

        // if we have a format from the
        // route we need to make sure
        if (array_key_exists('_format', $params)) {
            $this->request->setRequestFormat($params['_format']);
        }

        // resp
        try {

            // run before
            $this->before();

            // check if the current response 
            // is already ready to send, if yes
            // skip build and after
            if ($this->response->isReadyToSend() === false) {

                // build
                $resp = $this->build($params);
        
                // after
                $this->after();

            }

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
        // is this a response object that
        // isn't our response
        else if (is_a($resp, 'bolt\http\response') && $resp !== $this->response) {
            return $resp;
        }

        // use the layout
        if ($this->getUseLayout() && $this->layout) {
            $this->response->setLayout(function($content, $resp){
               return $this->view($this->layout, ['yield' => $content], $this)->render();
            });
        }

        // fire our after event
        $this->fire('afterRun');

        // give back the response to the http kernal
        return $this->response;
    }

}
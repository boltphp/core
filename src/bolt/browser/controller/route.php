<?php

namespace bolt\browser\controller;
use \bolt\browser;
use \b;

use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

/**
 * route controller
 */
class route extends browser\controller implements browser\router\face {

    /**
     * @var bolt\browser\response\format[]
     */
    private $_formats = [];

    /**
     * @var bolt\browser\response
     */
    protected $_response = false;

    /**
     * @var bolt\browser\request
     */
    protected $_request = false;

    /**
     * Construct
     *
     * @param bolt\browser
     *
     */
    final public function __construct(\bolt\browser $browser, \bolt\browser\request $req = null, \bolt\browser\response $resp = null) {
        parent::__construct($browser);

        $this->_request = $req ?: $browser->getRequest();
        $this->_response = $resp ?: $browser->getResponse();

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
                break;
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
     * @return bolt\browser\response\format
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
            $class = 'bolt\browser\response\format\\'.$format;
        }

        if (!class_exists($class, true)) {
            throw new \Exception("Unknown format class $class");
            return;
        }

        $o = new $class($this->response);


        if (!is_subclass_of($o, 'bolt\browser\response\format\face')) {
            throw new \Exception('Format class does not implement bolt\browser\response\format\face');
            return;
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
            'bolt\browser\request' => $this->request,
            'bolt\browser\response' => $this->response
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
     * @return bolt\browser\response
     */
    public function run($params) {


        $this->fire('beforeRun');

        // run before
        $this->before();

        // resp
        try {
            $resp = $this->build($params);
        }
        catch (\Exception $e) {
            $this->request->is404(true);
            return $this->response;
        }

        $this->after();

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
        else if ($resp instanceof \bolt\browser\views\view) {
            $content = $resp->render();
        }
        else if (is_a($resp, 'bolt\browser\response') AND $resp !== $this->response) {
            return $resp;
        }

        // if the build function set content and
        // we don't have any formats set
        // assume they set the default format
        if ($content !== "" AND count($this->_formats) !== 0 AND isset($params["_format"])) {
            $this->_formats[$params["_format"]] = $this->response->getContent();
        }

        // if _format exists in response. no we return
        if (array_key_exists('_format', $params) AND array_key_exists($params['_format'], $this->_formats)) {
            $content = $this->_formats[$params['_format']];
        }
        else if (array_key_exists('_format', $params)) {
            throw new \ResourceNotFoundException("Unable to match response", 404);
            return;
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
        if ($this->getUseLayout() AND $this->layout) {
            $content = $this->browser['views']->layout($this->layout, ['yield' => $content], $this)->render();
        }

        // set our content in the response
        $this->response->setContent($content);

        $this->fire('afterRun');

        return $this->response;
    }

}
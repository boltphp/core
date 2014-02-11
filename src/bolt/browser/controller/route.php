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
     * @var bolt\application
     */
    private $_app;

    /**
     * @var bolt\browser
     */
    private $_browser;

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
    final public function __construct(\bolt\browser $browser) {

        $this->_browser = $browser;
        $this->_app = $browser->app;

        $this->init();

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
            case 'app':
                return $this->_app;
            case 'browser':
                return $this->_browser;
            case 'request':
                return $this->_browser->getRequest();
            case 'response':
                return $this->_browser->getResponse();

        };

        return null;
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


    /**
     * run the controller
     *
     * @param array $params
     *
     * @return bolt\browser\response
     */
    public function run($params) {

        $this->before();

        // resp
        $resp = $this->build($params);

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

        // if our content is callable
        // we want to do that now
        while(is_callable($content)) {
            $content = call_user_func($content);
        }

        // use the layout
        if ($this->_useLayout AND $this->layout) {
            $content = $this->browser['views']->layout($this->layout, ['yield' => $content], $this)->render();
        }

        // set our content in the response
        $this->response->setContent($content);

        return $this->response;
    }

}
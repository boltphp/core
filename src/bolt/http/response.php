<?php

namespace bolt\http;
use \b;

/// require symfony response
use Symfony\Component\HttpFoundation\Response as SymfonyResponse,
    Symfony\Component\HttpFoundation\Request as SymfonyRequest,
    Symfony\Component\HttpFoundation\Cookie
;

/**
 * response object
 */
class response extends SymfonyResponse {

    /**
     * unique id for this response
     *
     * @var string
     */
    private $_guid;

    /**
     * is response ready to send
     *
     * @var boolean
     */
    private $_readyToSend = false;

    /**
     * exception attached to response
     *
     * @var Exception
     */
    private $_exception = null;

    /**
     * formats of this response
     *
     * @var array
     */
    private $_formats = [];

    /**
     * layout
     *
     * @var Closure
     */
    private $_layout = false;


    /**
     * Constructor
     */
    public function __construct() {
        $this->_guid = b::guid('req');
        call_user_func_array([get_parent_class(), '__construct'], func_get_args());
    }

    public function guid() {
        return $this->_guid;
    }


    /**
     * add a response format
     *
     * @param string|array $format format name or array of formats
     * @param mixed $content
     * @param bolt\http\controller $controller optional controller to tie to format
     *
     * @return bolt\http\response\format
     */
    public function format($format, $content=null, \bolt\http\controller $controller = null) {

        if (is_array($format)) {
            array_walk($format, function($content, $format) use ($controller){
                $this->format($format, $content, $controller);
            });
            return $this;
        }

        $class = $format;

        if (is_a($content,'bolt\http\response\format')) {
            $this->_formats[$format] = $content;
            $content = null;
        }
        else {
            if (!class_exists($class, true)) {
                $class = 'bolt\http\response\format\\'.$format;
            }
            if (!class_exists($class, true)) {
                throw new \Exception("Unknown format class $class");
            }

            $this->_formats[$format] = new $class($this, $controller);
        }


        if (!is_subclass_of($this->_formats[$format], 'bolt\http\response\format\face')) {
            throw new \Exception('Format class does not implement bolt\http\response\format\face');
        }

        if ($content !== null) {
            $this->_formats[$format]->setContent($content);
        }


        return $this->_formats[$format];
    }

    public function setCookie($cookie) {
        if (!is_a($cookie, 'Symfony\Component\HttpFoundation\Cookie')) {
            $cookie = new Cookie(
                b::param('name', null, $cookie),
                b::param('value', null, $cookie),
                b::param('expire', null, $cookie),
                b::param('path', '/', $cookie),
                b::param('domain', null, $cookie),
                b::param('secure', false, $cookie),
                b::param('http', true, $cookie)
            );
        }
        $this->headers->setCookie($cookie);
        return $this;
    }


    /**
     * return all registered formats
     *
     * @param  string $format
     *
     * @return null|bolt\http\response\format
     */
    public function getFormat($format) {
        return isset($this->_formats[$format]) ? $this->_formats[$format] : null;
    }


    /**
     * does this response have a foramt
     *
     * @param string $format
     *
     * @return boolean
     */
    public function hasFormat($format) {
        return isset($this->_formats[$format]);
    }

    /**
     * set a layout handler for this response
     *
     * @param mixed $layout string or callback
     *
     * @return self
     */
    public function setLayout($layout) {
        $this->_layout = $layout;
        return $this;
    }


    /**
     * get the current layout
     *
     * @return mixed
     */
    public function getLayout() {
        return $this->_layout;
    }


    /**
     * set a response exception
     *
     * @param Exception $e [description]
     *
     * @return self
     */
    public function setException(\Exception $e) {
        $this->_exception = $e;
        if (array_key_exists($e->getCode(), SymfonyResponse::$statusTexts)) {
            $this->setStatusCode($e->getCode());
        }
        return $this;
    }


    /**
     * does the response have an exception
     *
     * @return boolean
     */
    public function hasException() {
        return $this->_exception !== null;
    }


    /**
     * get eh response exception
     *
     * @return mixed Exception or null
     */
    public function getException() {
        return $this->_exception;
    }


    /**
     * get or set the response $_readyToSend
     *
     * @param  boolean $ready
     *
     * @return boolean
     */
    public function isReadyToSend($ready = null) {
        return $ready === null ? $this->_readyToSend : $this->_readyToSend = $ready;
    }


    /**
     * set a header
     *
     * @param string $name header name
     * @param string|array $value value of header
     * @param bool $replace
     *
     * @return self
     */
    public function setHeader($name, $value, $replace = true) {
        $this->headers->set($name, $value, $replace);
        return $this;
    }

    /**
     * set response as ready to send
     *
     * @return self
     */
    public function readyToSend() {
        $this->_readyToSend = true;
        return $this;
    }


    /**
     * prepare the response to be output
     *
     * @param  SymfonyRequest $request
     *
     * @return SymfonyResponse
     */
    public function prepare(SymfonyRequest $request) {
        $content = $this->getContent();

        // if this response is a redirect
        // there's no point in doing anything
        // below this line
        if ($this->isRedirection()) {
            return parent::prepare($request);
        }

        // no format use the set
        $format = $request->getRequestFormat();

        // make sure we have this format
        if (count($this->_formats) > 0 && !array_key_exists($format, $this->_formats)) {
            $this->setException(new \Exception("Unable to match response", 404));
            return $this;
        }

        // if format and we have this format
        // override our content
        if (array_key_exists($format, $this->_formats)) {

            // set the contnet
            $content = $this->_formats[$format];

            // set any headers we have
            foreach ($content->headers->all() as $name => $value) {
                if ($name == 'cache-control') {continue;}
                $this->headers->set($name, $value);
            }

        }

        // if our content is callable
        // we want to do that now
        while(is_callable($content)) {
            $content = call_user_func($content, $this);
        }

        // if we have a layout and it's callable
        if ($this->_layout && is_callable($this->_layout)) {
            $content = call_user_func($this->_layout, $content, $this);
        }

        // set the contnet
        $this->setContent($content);


        // run our parent prepare
        return parent::prepare($request);

    }

}
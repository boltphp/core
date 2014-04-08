<?php

namespace bolt\http;
use \b;

/// require symfony response
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

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

        $o = new $class($this);


        if (!is_subclass_of($o, 'bolt\http\response\format\face')) {
            throw new \Exception('Format class does not implement bolt\http\response\format\face');
        }

        $this->_formats[$format] = $o;
        $this->_formats[$format]->setContent($content);
        return $this->_formats[$format];
    }


    /**
     * does this response have a foramt
     *
     * @param string $format
     *
     * @return boolean
     */
    public function hasFormat($format) {
        return array_key_exists($format, $this->_formats);
    }


    public function setLayout($layout) {
        $this->_layout = $layout;
        return $this;
    }

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
        $this->readyToSend();
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
     * set response as ready to send
     *
     * @return self
     */
    public function readyToSend() {
        $this->_readyToSend = true;
        return $this;
    }


    public function prepare(SymfonyRequest $request) {
        $content = $this->getContent();

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
            $content = $this->_formats[$format];
        }

        // if our content is callable
        // we want to do that now
        while(is_callable($content)) {
            $content = call_user_func($content);
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
<?php

class formatTest extends Test {

    public function setUp() {
        $this->resp = new bolt\http\response();
        $this->f = new formatTest_Class($this->resp);
    }

    public function test_construct() {
        $this->assertTrue(in_array('bolt\http\response\format\face', class_implements($this->f)));
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\ResponseHeaderBag', $this->f->headers);
    }

    public function test_setHeader() {
        $this->eq($this->f, $this->f->setHeader('test', 'test'));
        $this->eq('test', $this->f->headers->get('test'));
    }

    public function test_getSetContent() {
        $this->eq(null, $this->f->getContent());
        $this->eq($this->f, $this->f->setContent('test'));
        $this->eq('test', $this->f->getContent());
    }

    public function test_invokeWithHeadersNoFormat() {
        $this->eq(null, $this->resp->headers->get('test'));
        $this->f->headers->set('test', 'test');
        $this->f->setContent('test');
        $this->eq('test', $this->f->__invoke());
        $this->eq(null, $this->resp->headers->get('Content-Type'));
    }

    public function test_invokeWithFormat() {
        $f = new formatTest_ClassFormat($this->resp);
        $f->setContent('test');
        $this->eq('atestb', $f->__invoke());
    }

    public function test_invokeCallback() {
        $this->f->setContent(function(){
            return function() {
                return 'test';
            };
        });
        $this->eq('test', $this->f->__invoke());
    }

}

class formatTest_Class extends bolt\http\response\format {

    public function getParent() {
        return $this->_parent;
    }

}

class formatTest_ClassFormat extends bolt\http\response\format {

    protected function format($str) {
        return 'a'.$str.'b';
    }

}
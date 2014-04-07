<?php

class responseTest extends Test {

    public function setUp(){
        $this->resp = new \bolt\http\response();
    }


    public function test_inherits() {
        $this->assertTrue( in_array('Symfony\Component\HttpFoundation\Response', class_parents($this->resp)) );
    }

    public function test_setGetGoodException() {
        $this->eq(null, $this->resp->getException());
        $e = new Exception('bad', 500);
        $this->eq($this->resp, $this->resp->setException($e));
        $this->eq($e, $this->resp->getException());
    }

    public function test_setBadException() {
        $this->setExpectedException('Exception');
        $o = new StdClass();
        $this->resp->setException($o);
    }

    public function test_hasException() {
        $this->eq(false, $this->resp->hasException());
        $this->resp->setException(new Exception(""));
        $this->eq(true, $this->resp->hasException());
    }

}
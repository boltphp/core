<?php

class requestTest extends Test {

    public function setUp() {
        $this->req = new \bolt\http\request();
    }

    public function test_inherits() {
        $this->assertTrue( in_array('Symfony\Component\HttpFoundation\Request', class_parents($this->req)) );
    }

    public function test_getContext() {
        $this->assertInstanceOf('Symfony\Component\Routing\RequestContext', $this->req->getContext());
    }

}
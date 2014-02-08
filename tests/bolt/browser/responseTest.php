<?php

class responseTest extends Test {

    public function setUp(){
        $this->resp = new \bolt\browser\response();
    }


    public function test_inherits() {
        $this->assertTrue( in_array('Symfony\Component\HttpFoundation\Response', class_parents($this->resp)) );
    }

}
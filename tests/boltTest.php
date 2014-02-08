<?php

class boltTest extends Test {

    public function setUp() {
        $this->bolt = new bolt();
    }

    public function test_init() {

        $this->assertInstanceOf('\bolt\application',  $this->bolt->init());

    }

    public function test_env() {
        $this->assertEquals('dev', $this->bolt->env());
        $this->assertEquals('prod', $this->bolt->env('prod'));
        $this->assertEquals('prod', $this->bolt->env());
    }

    public function test_guid() {
        $this->assertEquals('bolt9', $this->bolt->guid());
        $this->assertEquals('bolt10', $this->bolt->guid());
        $this->assertEquals('x11', $this->bolt->guid('x'));
    }

    public function test_helpers() {
        $this->assertEquals([], $this->bolt->getHelpers());

        $this->assertEquals($this->bolt, $this->bolt->helpers('boltTest_helperClass'));

        $this->assertEquals('9', $this->bolt->testHelperClass());

        $this->assertEquals(false, $this->bolt->notAHelperClass());

    }

}


class boltTest_helperClass {

    public function testHelperClass() {
        return '9';
    }

}
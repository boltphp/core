<?php

class routeTest extends Test {


    public function setUp() {
        $this->a = new bolt\application();
        $this->b = new bolt\browser($this->a);
        $this->c = new bolt\browser\controller\route($this->b);
    }

    public function test_get() {
        $this->eq($this->a, $this->c->app);
        $this->eq($this->b, $this->c->browser);
        $this->eq($this->b->request, $this->c->request);
        $this->eq($this->b->response, $this->c->response);
        $this->assertNull($this->c->poop);
    }

    public function test_formatCustomFormat() {
        $this->assertInstanceOf('routeTest_CustomFormat', $this->c->format('routeTest_CustomFormat', 'test'));
    }

    public function test_formatDefaultFormat() {
        $this->assertInstanceOf('bolt\browser\response\format\html', $this->c->format('html', 'test'));
    }

    public function test_formatBadDefault() {
        $this->setExpectedException('Exception');
        $this->c->format('NOOO', 'test');
    }

    public function test_formatBadCustom() {
        $this->setExpectedException('Exception');
        $this->c->format('routeTest_BadClass', 'test');
    }

}

class routeTest_CustomFormat extends bolt\browser\response\format {

}

class routeTest_BadClass {

}
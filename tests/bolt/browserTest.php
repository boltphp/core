<?php

class browserTest extends Test {

    public function test_start() {
        $this->assertInstanceOf('bolt\browser', \bolt\browser::start([]));
    }

    public function test_start_withReqRes() {
        $req = new bolt\browser\request();
        $res = new bolt\browser\response();

        $b = bolt\browser::start([
                'request' => $req,
                'response' => $res
            ]);

        $this->assertEquals($req->bguid(), $b->getRequest()->bguid());
        $this->assertEquals($res->bguid(), $b->getResponse()->bguid());

    }


    public function test_getResponse() {
        $b = new bolt\browser();
        $this->assertInstanceOf('bolt\browser\response', $b->getResponse());
    }

    public function test_getRequest() {
        $b = new bolt\browser();
        $this->assertInstanceOf('bolt\browser\request', $b->getRequest());
    }

    public function test_getSetPaths() {
        // $b = new bolt\browser();
        // $this->assertFalse($b->getPath('nope'));

        // $b->path('test', '/poop');
        // $b->path(['test1' => '/poop1', 'test2' => 'poop2']);

        // $this->assertEquals('/poop', $b->getPath('test'));
        // $this->assertEquals('/poop1', $b->getPath('test1'));
        // $this->assertEquals('/poop2', $b->getPath('test2'));

    }

}
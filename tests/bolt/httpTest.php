<?php

class httpTest extends Test {

    public function setUp() {
        $this->app = new bolt\application();
        $this->http = new \bolt\http($this->app);
    }

    public function test_inherits() {
        $this->assertTrue( in_array('bolt\helpers\events', class_uses($this->http)) );
    }

    public function test_constructWithReqRes() {
        $req = new \bolt\http\request();
        $resp = new \bolt\http\response();

        $b = new bolt\http($this->app, $req, $resp);

        $this->eq($req, $b->getRequest());
        $this->eq($resp, $b->getResponse());
    }

    public function test_getSetRequest() {
        $this->assertInstanceOf('\bolt\http\request', $this->http->getRequest());
        $r = new bolt\http\request();
        $this->eq($this->http, $this->http->setRequest($r));
        $this->eq($r, $this->http->getRequest());
    }

    public function test_getSetResponse() {
        $this->assertInstanceOf('\bolt\http\response', $this->http->getResponse());
        $r = new bolt\http\response();
        $this->eq($this->http, $this->http->setResponse($r));
        $this->eq($r, $this->http->getResponse());
    }

    public function test_magicGet() {
        $this->eq($this->http->getApp(), $this->http->app);
        $this->eq($this->http->getRequest(), $this->http->request);
        $this->eq($this->http->getResponse(), $this->http->response);
        $this->assertFalse($this->http->not_for_real);
    }

    public function test_runMiddelware() {
        $this->eq($this->http, $this->http->runMiddleware('handle'));
    }

    public function test_runMiddlewareByName() {
        $this->setExpectedException('Exception');
        $this->http->runMiddlewareByName('poop', 'poop');
    }

    public function test_bindCallback() {
        $called = false;
        $http = $this->http;
        $cb = function($req, $resp) use ($http, &$called) {
            $this->assertEquals($http->request, $req);
            $this->assertEquals($http->response, $resp);
            $called = true;
        };

        $this->eq($this->http, $this->http->bind($cb));

        $this->http->runMiddleware('handle');

        $this->assertTrue($called);

    }

    public function test_bindArray() {
        $called = 0;
        $cb1 = function() use (&$called) {
            $called++;
        };
        $cb2 = function() use (&$called) {
            $called++;
        };

        $this->http->bind([$cb1, $cb2]);

        $this->http->runMiddleware('handle');

        $this->eq(2, $called);

    }

    public function test_bindObject() {
        $m = new httpTest_testMiddleware($this->http);
        $this->http->bind('test', $m);
        $this->assertTrue($this->http->runMiddlewareByName('test', 'handle'));
    }

    public function test_bindClass() {
        $this->http->bind('test', 'httpTest_testMiddleware');
        $this->assertTrue($this->http->runMiddlewareByName('test', 'handle'));
    }


    public function test_bindWithClosure() {
        $called = false;
        $cb = function() use (&$called) {
            $called = true;
        };
        $this->eq($this->http, $this->http->bind('handle', $cb));
        $this->http->runMiddleware('handle');
        $this->assertTrue($called);
    }

    public function test_runNoRouter() {
        $i = new httpTest_testMiddlewareFull($this->http);
        $this->http->bind('test', $i);
        $this->assertFalse($i->beforeRun);
        $this->assertFalse($i->handleRun);
        $this->assertFalse($i->afterRun);
        $this->http->execute();
        $this->assertTrue($i->beforeRun);
        $this->assertTrue($i->handleRun);
        $this->assertTrue($i->afterRun);

        $this->assertFalse($i->hasController);
    }

    public function test_runWithRouter() {
        $this->http['router'] = 'bolt\http\router';

        $this->http['router']->get('/', function(){

        });

        $i = new httpTest_testMiddlewareFullWithController($this->http);
        $this->http->bind('test', $i);

        $this->assertFalse($i->beforeRun);
        $this->assertFalse($i->handleRun);
        $this->assertFalse($i->afterRun);
        $this->http->execute();
        $this->assertTrue($i->beforeRun);
        $this->assertTrue($i->handleRun);
        $this->assertTrue($i->afterRun);
        $this->assertTrue($i->hasController);

    }


    public function test_path() {
        $this->app->setRoot(__DIR__);
        $this->assertEquals(__DIR__."/test_aa", $this->http->path('test_aa/'));
    }

    public function test_load() {
        $this->eq($this->http, $this->http->load('test', __DIR__));
    }

    public function test_run(){
        $this->eq($this->http, $this->http->run());
        $this->assertTrue($this->http->getApp()->hasRun());
    }

}

class httpTest_testMiddleware extends \bolt\http\middleware {

    public function handle($request, $response) {
        return true;
    }

}


class httpTest_testMiddlewareFull extends \bolt\http\middleware {

    public $beforeRun = false;
    public $handleRun = false;
    public $afterRun = false;
    public $hasController = null;

    public function before() {
        $this->beforeRun = true;
    }

    public function handle($controller=null) {
        $this->handleRun = true;
        $this->hasController = !$controller === null;
    }

    public function after() {
        $this->afterRun = true;
    }

}

class httpTest_testMiddlewareFullWithController extends httpTest_testMiddlewareFull {


    public function handle($controller=null) {
        $this->handleRun = true;
        $this->hasController = $controller !== null;
    }
}
<?php

class browserTest extends Test {

    public function setUp() {
        $this->app = new bolt\application();
        $this->browser = new \bolt\browser($this->app);
    }

    public function test_inherits() {
        $this->assertTrue( in_array('bolt\helpers\events', class_uses($this->browser)) );
    }

    public function test_constructWithReqRes() {
        $req = new \bolt\browser\request();
        $resp = new \bolt\browser\response();

        $b = new bolt\browser($this->app, $req, $resp);

        $this->eq($req, $b->getRequest());
        $this->eq($resp, $b->getResponse());
    }

    public function test_getSetRequest() {
        $this->assertInstanceOf('\bolt\browser\request', $this->browser->getRequest());
        $r = new bolt\browser\request();
        $this->eq($this->browser, $this->browser->setRequest($r));
        $this->eq($r, $this->browser->getRequest());
    }

    public function test_getSetResponse() {
        $this->assertInstanceOf('\bolt\browser\response', $this->browser->getResponse());
        $r = new bolt\browser\response();
        $this->eq($this->browser, $this->browser->setResponse($r));
        $this->eq($r, $this->browser->getResponse());
    }

    public function test_magicGet() {
        $this->eq($this->browser->getApp(), $this->browser->app);
        $this->eq($this->browser->getRequest(), $this->browser->request);
        $this->eq($this->browser->getResponse(), $this->browser->response);
        $this->assertFalse($this->browser->not_for_real);
    }

    public function test_runMiddelware() {
        $this->eq($this->browser, $this->browser->runMiddleware('handle'));
    }

    public function test_runMiddlewareByName() {
        $this->setExpectedException('Exception');
        $this->browser->runMiddlewareByName('poop', 'poop');
    }

    public function test_bindCallback() {
        $called = false;
        $browser = $this->browser;
        $cb = function($req, $resp) use ($browser, &$called) {
            $this->assertEquals($browser->request, $req);
            $this->assertEquals($browser->response, $resp);
            $called = true;
        };

        $this->eq($this->browser, $this->browser->bind($cb));

        $this->browser->runMiddleware('handle');

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

        $this->browser->bind([$cb1, $cb2]);

        $this->browser->runMiddleware('handle');

        $this->eq(2, $called);

    }

    public function test_bindObject() {
        $m = new browserTest_testMiddleware($this->browser);
        $this->browser->bind('test', $m);
        $this->assertTrue($this->browser->runMiddlewareByName('test', 'handle'));
    }

    public function test_bindClass() {
        $this->browser->bind('test', 'browserTest_testMiddleware');
        $this->assertTrue($this->browser->runMiddlewareByName('test', 'handle'));
    }


    public function test_bindWithClosure() {
        $called = false;
        $cb = function() use (&$called) {
            $called = true;
        };
        $this->eq($this->browser, $this->browser->bind('handle', $cb));
        $this->browser->runMiddleware('handle');
        $this->assertTrue($called);
    }

    public function test_runNoRouter() {
        $i = new browserTest_testMiddlewareFull($this->browser);
        $this->browser->bind('test', $i);
        $this->assertFalse($i->beforeRun);
        $this->assertFalse($i->handleRun);
        $this->assertFalse($i->afterRun);
        $this->browser->execute();
        $this->assertTrue($i->beforeRun);
        $this->assertTrue($i->handleRun);
        $this->assertTrue($i->afterRun);

        $this->assertFalse($i->hasController);
    }

    public function test_runWithRouter() {
        $this->browser['router'] = 'bolt\browser\router';

        $this->browser['router']->get('/', function(){

        });

        $i = new browserTest_testMiddlewareFullWithController($this->browser);
        $this->browser->bind('test', $i);

        $this->assertFalse($i->beforeRun);
        $this->assertFalse($i->handleRun);
        $this->assertFalse($i->afterRun);
        $this->browser->execute();
        $this->assertTrue($i->beforeRun);
        $this->assertTrue($i->handleRun);
        $this->assertTrue($i->afterRun);
        $this->assertTrue($i->hasController);

    }


    public function test_path() {
        $this->app->setRoot(__DIR__);
        $this->assertEquals(__DIR__."/test_aa", $this->browser->path('test_aa/'));
    }

    public function test_load() {
        $this->eq($this->browser, $this->browser->load('test', __DIR__));
    }

    public function test_run(){
        $this->eq($this->browser, $this->browser->run());
        $this->assertTrue($this->browser->getApp()->hasRun());
    }

}

class browserTest_testMiddleware extends \bolt\browser\middleware {

    public function handle($request, $response) {
        return true;
    }

}


class browserTest_testMiddlewareFull extends \bolt\browser\middleware {

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

class browserTest_testMiddlewareFullWithController extends browserTest_testMiddlewareFull {


    public function handle($controller=null) {
        $this->handleRun = true;
        $this->hasController = $controller !== null;
    }
}
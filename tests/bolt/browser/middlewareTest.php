<?php

class middlewareTest extends Test {

    public function setUp() {
        $this->a = new bolt\application();
        $this->b = new bolt\browser($this->a);
        $this->m = new middlewareTest_Class($this->b);
    }

    public function test_init() {
        $this->assertTrue($this->m->initRun);
    }

    public function test_execute() {
        $this->assertFalse($this->m->testRun);
        $this->m->execute('test', ['test' => 'poop']);
        $this->assertTrue($this->m->testRun);
    }

    public function test_getArgsFromMethodRef() {
        $func = function(
            bolt\browser\request $rq,
            bolt\browser\response $re,
            bolt\browser $br,
            bolt\application $ap,
            $request,
            $response,
            $browser,
            $app,
            $args,
            $test,
            $nope = true
        ) {};
        $ref = new ReflectionFunction($func);
        $args = $this->m->test_getArgsFromMethodRef($ref, ['test' => 'poop']);

        $this->eq([
                $this->b->request,
                $this->b->response,
                $this->b,
                $this->a,
                $this->b->request,
                $this->b->response,
                $this->b,
                $this->a,
                ['test' => 'poop'],
                'poop',
                true
            ], $args);

    }


    public function test_badRefGetArgsFromMethodRef() {
        $this->setExpectedException('Exception');
        $this->m->test_getArgsFromMethodRef('nope', []);
    }

}


class middlewareTest_Class extends bolt\browser\middleware {

    public $initRun = false;
    public $testRun = false;

    public function init() {
        $this->initRun = true;
    }

    public function test($test) {
        $this->testRun = $test == 'poop';
    }

    public function test_getArgsFromMethodRef() {
        return call_user_func_array([$this, 'getArgsFromMethodRef'], func_get_args());
    }

}
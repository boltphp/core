<?php

class controllerTest extends Test {

    public function setUp() {
        $this->a = new \bolt\application();
        $this->b = new \bolt\browser($this->a);
    }

    public function test_set() {
        $c = new bolt\browser\controller($this->b);
        $c->test = 'test';
        $this->eq('test', $c->getParameters()['test']);
    }

    public function test_getParameters() {
        $c = new bolt\browser\controller($this->b);
        $this->eq([], $c->getParameters());
        $c->test = 'x';
        $this->eq(['test' => 'x'], $c->getParameters());
    }

    public function test_getArgsFromMethodRef() {
        $class = new StdClass();
        $func = function(stdClass $cl, $test, $nope=true) {};
        $params = [
            'test' => 'test'
        ];

        $c = new controllerTest_test($this->b);
        $ref = new ReflectionFunction($func);

        $args = $c->test_getArgsFromMethodRef($ref, $params, ['stdClass' => $class]);

        $this->assertEquals([
                $class,
                $params['test'],
                true
            ], $args);

    }

    public function test_badRefGetArgsFromMethodRef() {
        $this->setExpectedException('Exception');
        $c = new controllerTest_test();
        $c->test_getArgsFromMethodRef('nope', [], []);
    }

}

class controllerTest_test extends \bolt\browser\controller {

    public function test_getArgsFromMethodRef() {
        return call_user_func_array([$this, 'getArgsFromMethodRef'], func_get_args());
    }

}
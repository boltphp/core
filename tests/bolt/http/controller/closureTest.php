<?php

class closureTest extends Test {

    public function setUp() {
        $a = new bolt\application();
        $b = new bolt\http($a);
        $this->c = new bolt\http\controller\closure($b);
    }

    public function test_inherits() {
        $this->assertTrue( in_array('bolt\http\controller\route', class_parents($this->c)) );
    }

    public function test_buildWithClosure() {
        $pass = false;

        $cb = function() use (&$pass){
            $pass = true;
            return 'poop';
        };

        $this->eq('poop', $this->c->build(['_closure' => $cb]));

        $this->assertTrue($pass);

    }

    public function test_buildNoClosure() {
        $this->setExpectedException('Exception');
        $this->c->build([]);
    }


    public function test_buildBadClosure() {
        $this->setExpectedException('Exception');
        $this->c->build(['_closure' => false]);
    }

}
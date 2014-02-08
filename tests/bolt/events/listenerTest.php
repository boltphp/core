<?php

class listenerTest extends Test {

    public function setUp() {
        $this->parent = new listenerTest_Class();
    }

    public function test_constructWithContext() {
        $ctx = new StdClass();
        $args = ['text' => 1];
        $cb = function() { };

        $l = new bolt\events\listener($this->parent, $cb, 'test', $args, $ctx);

        $this->assertEquals($this->parent, $l->parent);
        $this->assertEquals($cb, $l->callback);
        $this->assertEquals($args, $l->args);
        $this->assertEquals($ctx, $l->context);

        $this->assertFalse($l->guid === "");

        $this->assertNull($l->poop);

    }

    public function test_constructBadParent() {
        $this->setExpectedException('Exception');
        new bolt\events\listener(new StdClass, function(){}, 'test');
    }

    public function test_constructBadContext() {
        $l = new bolt\events\listener($this->parent, function() {}, 'test', 'test');
        $this->assertEquals($this->parent, $l->parent);
    }

    public function test_once() {
        $l = new bolt\events\listener($this->parent, function() {}, 'test', 'test');
        $this->assertFalse($l->once);
        $l->once(true);
        $this->assertTrue($l->once);
        $l->once(false);
        $this->assertFalse($l->once);
    }

    public function test_context() {
        $ctx1 = new StdClass();

        $l = new bolt\events\listener($this->parent, function() {}, 'test');

        $l->context($ctx1);

        $this->assertEquals($ctx1, $l->context);

        $ref = new ReflectionFunction($l->callback);

        $this->assertEquals($ctx1, $ref->getClosureThis());

    }

    public function test_execute() {
        $run = false;
        $cb = function() use (&$run) {
            $run = true;
        };

        $l = new bolt\events\listener($this->parent, $cb, 'test');

        $e = new bolt\events\event($l, []);

        $l->execute($e);

        $this->assertTrue($run);

    }

}

class listenerTest_Class {
    use bolt\events;

}

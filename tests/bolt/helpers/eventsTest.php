<?php

class helpers_eventsTest extends Test {

    public function setUp(){
        $this->cb = function() {
            return;
        };
        $this->c = new eventsTest_testClass();
    }

    public function test_addListener() {
        $l = $this->c->addListener('test', $this->cb);
        $this->eq([$l], $this->c->getListeners('test'));
        $this->assertInstanceOf('bolt\helpers\events\listener', $l);
    }

    public function test_removeListener() {
        $this->eq([], $this->c->getListeners('test'));
        $l = $this->c->addListener('test', $this->cb);
        $this->eq([$l], $this->c->getListeners('test'));
        $this->c->removeListener($l);
        $this->eq([], $this->c->getListeners('test'));
    }

    public function test_getListeners() {
        $this->eq([], $this->c->getListeners('test'));
        $l1 = $this->c->addListener('test', $this->cb);
        $l2 = $this->c->addListener('test1', $this->cb);
        $this->eq([$l1], $this->c->getListeners('test'));
    }

    public function test_getAllListeners() {
        $this->eq([], $this->c->getAllListeners());
        $l1 = $this->c->addListener('test', $this->cb);
        $l2 = $this->c->addListener('test1', $this->cb);
        $this->eq(['test' => [$l1], 'test1' => [$l2]], $this->c->getAllListeners());
        $this->c->removeListener($l2);
        $this->eq(['test' => [$l1], 'test1' => []], $this->c->getAllListeners());
    }

    public function test_on() {
        $l = $this->c->on('test', $this->cb);
        $this->eq([$l], $this->c->getListeners('test'));
        $this->assertInstanceOf('bolt\helpers\events\listener', $l);
    }

    public function test_off() {
        $this->eq([], $this->c->getListeners('test'));
        $l = $this->c->addListener('test', $this->cb);
        $this->eq([$l], $this->c->getListeners('test'));
        $this->c->off($l);
        $this->eq([], $this->c->getListeners('test'));
    }

    public function test_onceOn() {
        $run = 0;
        $cb = function() use (&$run) {
            $run++;
        };
        $l = $this->c->once('test', $cb);
        $this->c->fireEvent('test');
        $this->eq($run, 1);
        $this->c->fireEvent('test');
        $this->eq($run, 1);
    }

    public function test_fire() {
        $cb1 = function() {
            $this->event1Run = true;
        };
        $cb2 = function() {
            $this->event2Run = true;
        };

        $this->c->on('test1', $cb1);
        $this->c->on('test2', $cb2);

        $this->c->fireEvent('test');

        $this->assertFalse($this->c->event1Run);
        $this->assertFalse($this->c->event2Run);

        $this->c->fireEvent('test1');

        $this->assertTrue($this->c->event1Run);
        $this->assertFalse($this->c->event2Run);

        $this->c->fireEvent('test2');

        $this->assertTrue($this->c->event1Run);
        $this->assertTrue($this->c->event2Run);

    }

}

class eventsTest_testClass {
    use bolt\helpers\events;

    public $event1Run = false;
    public $event2Run = false;

    public function fireEvent($type) {
        $this->fire($type);
    }

}
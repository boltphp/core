<?php

class cliTest extends Test {

    public function setUp() {
        $this->a = $this->getApp();
        $this->c = new bolt\cli($this->a);
    }

    public function test_inherits() {
        $this->assertTrue(in_array('bolt\plugin', class_parents($this->c)));
        $this->assertTrue(in_array('bolt\helpers\events', class_uses($this->c)));
    }

    public function test_attachEvent() {
        $e = $this->c->getApp()->getListeners('run:cli');
        $this->eq(1, count($e));
        $this->eq([$this->c, 'execute'], $e[0]->callback);
    }

    public function test_getApp() {
        $this->eq($this->a, $this->c->getApp());
    }

    public function test_getConsole() {
        $this->assertInstanceOf('Symfony\Component\Console\Application', $this->c->getConsole());
    }

    public function test_getInput() {
        $this->assertInstanceOf('Symfony\Component\Console\Input\ArgvInput', $this->c->getInput());
    }

    public function test_getOutput() {
        $this->assertInstanceOf('Symfony\Component\Console\Output\ConsoleOutput', $this->c->getOutput());
    }

}
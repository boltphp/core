<?php

class applicationTest extends Test {

    public function setUp() {
        $this->app = new bolt\application();
    }

    public function test_inherits() {
        $this->assertTrue( in_array('bolt\plugin', class_parents($this->app)) );
        $this->assertTrue( in_array('bolt\events', class_uses($this->app)) );
    }

    public function test_root() {

        $this->assertEquals(TEST_ROOT, $this->app->getRoot());
        $this->assertEquals($this->app, $this->app->setRoot(__DIR__));
        $this->assertEquals(__DIR__, $this->app->getRoot());

    }

    public function test_path() {
        $this->assertEquals(TEST_ROOT."/test", $this->app->path('test'));
        $this->app->setRoot(__DIR__);
        $this->assertEquals(__DIR__."/test_aa", $this->app->path('test_aa/'));
    }

    public function test_load() {
        // TODO
    }

    public function test_run() {
        // TODO
    }

}
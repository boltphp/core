<?php

class viewsTest extends Test {

    public function setUp() {
        $this->a = new bolt\application(['root' => TEST_ROOT]);
        $this->b = new bolt\http($this->a);
        $this->v = new bolt\http\views($this->b, []);
    }

    public function test_constructWithEngines() {
        $v = new bolt\http\views($this->b, [
            'engines' => [
                ['html' ,'viewsTest_Engine']
            ]
        ]);
        $this->eq('viewsTest_Engine', $v->getEngines()['html']['class']);
    }

    public function test_getDirs() {
        $this->eq([], $this->v->getDirs());
        $this->v->dir('mock');
        $this->eq(['mock'], $this->v->getDirs());
    }

    public function test_dirString() {
        $this->eq($this->v, $this->v->dir('test'));
        $this->eq(['test'], $this->v->getDirs());
    }

    public function test_dirArray() {
        $this->eq($this->v, $this->v->dir(['test', 'test1']));
        $this->eq(['test','test1'], $this->v->getDirs());
    }


    public function test_engine() {
        $this->eq($this->v, $this->v->engine('html', 'viewsTest_Engine'));
        $this->eq('viewsTest_Engine', $this->v->getEngines()['html']['class']);
    }

    public function test_getEngines() {
        $this->eq([], $this->v->getEngines());
        $this->eq($this->v, $this->v->engine('html', 'viewsTest_Engine'));
        $this->eq(['html' => [
                'class' => 'viewsTest_Engine',
                'instance' => false,
                'canCompile' => false
            ]], $this->v->getEngines());
    }

    public function test_findGood() {
        $this->v->dir('mock');
        $this->eq(TEST_ROOT."/mock/test.html", $this->v->find("test.html"));
    }

    public function test_findBad() {
        $this->assertFalse($this->v->find("test.html", []));
    }

    public function test_exists() {
        $this->v->dir('mock');
        $this->assertTrue($this->v->exists("test.html"));
        $this->assertFalse($this->v->exists("test_NOPE.html"));
    }


    public function test_createGood() {
        $this->v->dir('mock');
        $this->v->engine('html', 'viewsTest_Engine');
        $this->assertInstanceOf('bolt\http\views\file', $this->v->create(TEST_ROOT."/mock/test.html"));
    }

    public function test_createNoFile() {
        $this->setExpectedException('Exception');
        $this->v->create(false);
    }

    public function test_createNoEngine() {
        $this->setExpectedException('Exception');
        $this->v->dir('mock');
        $this->v->engine('test', 'viewsTest_Engine');
        $this->v->create(TEST_ROOT."/mock/test.html");
    }

    public function test_createSingleEngineInstance() {
        $this->v->dir('mock');
        $this->v->engine('html', 'viewsTest_Engine');
        $this->assertInstanceOf('bolt\http\views\file', $this->v->create(TEST_ROOT."/mock/test.html"));

        $e = $this->v->getEngines()['html']['instance'];
        $v = $this->v->create(TEST_ROOT."/mock/test.html");
    }

}


class viewsTest_Engine extends \bolt\render\base {

    public function render($str, $vars = []) {

    }

}
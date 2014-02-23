<?php

class viewsTest extends Test {

    public function setUp() {
        $this->a = new bolt\application(['root' => TEST_ROOT]);
        $this->b = new bolt\browser($this->a);
        $this->v = new bolt\browser\views($this->b, []);
    }

    public function test_constructWithEngines() {
        $v = new bolt\browser\views($this->b, [
            'engines' => [
                ['html' ,'viewsTest_Engine']
            ]
        ]);
        $this->eq('viewsTest_Engine', $v->getEngines()['html']['class']);
    }

    public function test_getViewDirs() {
        $this->eq([], $this->v->getViewDirs());
        $this->v->dir('mock');
        $this->eq(['mock'], $this->v->getViewDirs());
        $this->v->dir("mock2", 'layouts');
        $this->eq(['mock'], $this->v->getViewDirs());
    }

    public function test_getLayoutDirs() {
        $this->eq([], $this->v->getLayoutDirs());
        $this->v->dir('mock', 'layouts');
        $this->eq(['mock'], $this->v->getLayoutDirs());
        $this->v->dir("mock2");
        $this->eq(['mock'], $this->v->getLayoutDirs());
    }

    public function test_dirString() {
        $this->eq($this->v, $this->v->dir('test'));
        $this->eq(['test'], $this->v->getViewDirs());
    }

    public function test_dirArray() {
        $this->eq($this->v, $this->v->dir(['test', 'test1']));
        $this->eq(['test','test1'], $this->v->getViewDirs());
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
                'instance' => false
            ]], $this->v->getEngines());
    }

    public function test_findGood() {
        $this->eq(TEST_ROOT."/mock/test.html", $this->v->find("test.html", ['mock']));
    }

    public function test_findBad() {
        $this->assertFalse($this->v->find("test.html", []));
    }

    public function test_exists() {
        $this->assertTrue($this->v->exists("test.html", ['mock']));
        $this->assertFalse($this->v->exists("test.html", []));
    }

    public function test_view() {
        $this->v->dir('mock');
        $this->v->engine('html', 'viewsTest_Engine');
        $this->assertInstanceOf('bolt\browser\views\view', $this->v->view('test.html', [], false));
    }

    public function test_layout() {
        $this->v->dir('mock', 'layouts');
        $this->v->engine('html', 'viewsTest_Engine');
        $this->assertInstanceOf('bolt\browser\views\view', $this->v->layout('test.html', [], false));
    }

    public function test_createGood() {
        $this->v->dir('mock');
        $this->v->engine('html', 'viewsTest_Engine');
        $this->assertInstanceOf('bolt\browser\views\view', $this->v->create(TEST_ROOT."/mock/test.html"));
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
        $this->assertInstanceOf('bolt\browser\views\view', $this->v->create(TEST_ROOT."/mock/test.html"));

        $e = $this->v->getEngines()['html']['instance'];
        $v = $this->v->create(TEST_ROOT."/mock/test.html");

        $this->eq($e, $v->getEngine());

    }

}


class viewsTest_Engine {


}
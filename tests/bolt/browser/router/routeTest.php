<?php

use bolt\browser\router\route;

class router_routeTest extends Test {


    public function test_inherits() {
        $r = new route('path');
        $this->assertTrue(in_array('bolt\browser\router\face', class_implements($r)));
        $this->assertTrue(in_array('Symfony\Component\Routing\Route', class_parents($r)));
    }

    public function test_staticCreate() {
        $r = route::create(['path' => 'test', 'name' => 'test']);
        $this->assertInstanceOf('bolt\browser\router\route', $r);
        $this->eq('/test', $r->getPath());
        $this->eq('test', $r->getName());
    }

    public function test_setGetName() {
        $r = new route('path');
        $this->eq($r, $r->setName('test'));
        $this->eq('test', $r->getName());
    }

    public function test_setGetControllerString() {
        $r = new route('path');
        $this->eq($r, $r->setController('test'));
        $this->eq('test', $r->getController());
    }

    public function test_setGetControllerCallback() {
        $c = function() {};
        $r = new route('path');
        $this->eq($r, $r->setController($c));
        $this->eq('\bolt\browser\controller\closure', $r->getController());
        $d = $r->getDefaults();
        $this->eq($c, $d['_closure']);
    }

    public function test_setRequire() {
        $r = new route('path');
        $this->eq($r, $r->setRequire(['test']));
        $this->eq(['test'], $r->getRequirements());
    }

    public function test_setAction() {
        $r = new route('path');
        $this->eq($r, $r->setAction('test'));
        $d = $r->getDefaults();
        $this->eq(['_action' => 'test'], $d);
    }

    public function test_setFormatsString() {
        $r = new route('path');
        $this->eq($r, $r->setFormats("test,test1"));
        $d = $r->getDefaults();
        $this->eq(['_formats' => ['test', 'test1']], $d);
    }

    public function test_setFormatsArray() {
        $r = new route('path');
        $this->eq($r, $r->setFormats(["?test","test1"]));
        $d = $r->getDefaults();
        $this->eq(['_formats' => ['test', 'test1'], '_format' => 'test'], $d);
    }

    public function test_compile() {
        $r = new route('path');
        $r->setFormats("test");
        $c = $r->compile();
        $this->assertInstanceOf('Symfony\Component\Routing\CompiledRoute', $c);
    }

}
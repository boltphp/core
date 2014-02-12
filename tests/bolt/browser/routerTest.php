<?php

class rotuerTest extends Test {

    public function setUp() {
        $this->a = new bolt\application();
        $this->b = new bolt\browser($this->a);
        $this->r = new bolt\browser\router($this->b);
    }

    public function test_add() {
        $r = new \bolt\browser\router\route('/root');
        $r->setName('test');
        $this->eq($this->r, $this->r->add($r));
        $this->eq($r, $this->r->getByName('test'));
    }

    public function test_getCollection() {
        $this->assertInstanceOf('bolt\browser\router\collection', $this->r->getCollection());
    }

    public function test_matchHasRoute() {
        $route = new \bolt\browser\router\route("/test");
        $req = \bolt\browser\request::create("http://localhost/test");
        $this->eq($this->r, $this->r->add($route));
        $this->assertTrue(is_array($this->r->match($req)));
    }

    public function test_matchNoRoute() {
        $this->setExpectedException('Exception');
        $req = \bolt\browser\request::create("http://localhost/test");
        $this->r->match($req);
    }

    public function test_getByName() {
        $route = new \bolt\browser\router\route("/test");
        $route->setName('tester');
        $this->eq($this->r, $this->r->add($route));
        $this->eq($route, $this->r->getByName('tester'));
        $this->eq(null, $this->r->getByName('nope'));
    }

    public function test_loadFromControllers() {
        $this->r->loadFromControllers();

        $this->eq(4, $this->r->getCollection()->count());

        $this->assertTrue($this->r->getByName('test1') !== null);
        $this->assertTrue($this->r->getByName('test2') !== null);
        $this->assertTrue($this->r->getByName('test4') !== null);

    }

}

class routerTest_ClassStaticProperty implements \bolt\browser\router\face {

    public static $routes = [
        ['path' => 'path1', 'name' => 'test1']
    ];

}

class routerTest_ClassStaticClass implements \bolt\browser\router\face {

    public static function getRoutes(){
        return [
            ['path' => 'path2', 'name' => 'test2'],
            ['path' => 'path3'],
            'test4' => ['path' => 'path4']
        ];
    }

}

class routerTest_Nope {


}
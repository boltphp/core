<?php

class collectionTest extends Test {

    public function test_extends() {
        $c = new bolt\browser\router\collection;
        $this->assertTrue(in_array('Symfony\Component\Routing\RouteCollection', class_parents($c)));
    }

    public function test_staticCreateEmpty() {
        $c = bolt\browser\router\collection::create();
        $this->assertInstanceOf('bolt\browser\router\collection', $c);
    }

    public function test_staticCreateWithRoutes() {
        $r = new bolt\browser\router\route('/test');
        $c = bolt\browser\router\collection::create([$r]);
        $this->eq(1, $c->count());
    }

}
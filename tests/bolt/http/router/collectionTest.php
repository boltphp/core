<?php

class collectionTest extends Test {

    public function test_extends() {
        $c = new bolt\http\router\collection;
        $this->assertTrue(in_array('Symfony\Component\Routing\RouteCollection', class_parents($c)));
    }

    public function test_staticCreateEmpty() {
        $c = bolt\http\router\collection::create();
        $this->assertInstanceOf('bolt\http\router\collection', $c);
    }

    public function test_staticCreateWithRoutes() {
        $r = new bolt\http\router\route('/test');
        $c = bolt\http\router\collection::create([$r]);
        $this->eq(1, $c->count());
    }

}
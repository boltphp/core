<?php

class viewsTest extends Test {

    public function setUp() {
        $this->a = new bolt\application();
        $this->b = new bolt\browser($this->a);
        $this->v = new bolt\browser\views($this->b, []);
    }

    public function test_dirString() {
        $this->eq($this->v, $this->v->dir('test'));
        $this->eq(['test'], $this->v->getDirs());
    }

    public function test_dirArray() {
        $this->eq($this->v, $this->v->dir(['test', 'test1']));
        $this->eq(['test','test1'], $this->v->getDirs());
    }

}
<?php

class rotuerTest extends Test {

    public function setUp() {
        $this->a = new bolt\application();
        $this->r = new bolt\browser\router($this->a);
    }

    public function test_add() {
        $r = new \bolt\browser\router\route('/root');
        $r->setName('test');
        $this->eq($this->r, $this->r->add($r));
        $this->eq($r, $this->r->getByName('test'));
    }

}
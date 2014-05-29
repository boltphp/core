<?php

class dom_elementTest extends Test {

    public function setUp() {
        $this->el = new bolt\dom\element("div", "poop");
    }

    public function testDefaultAttribute() {
        $this->eq($this->el->getAttribute('data-domref'), $this->el->guid);
    }

    public function testGetHtml() {
        $this->eq('<div>poop</div>', $this->el->html());
    }

}
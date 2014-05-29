<?php

class dom_collectionTest extends Test {

    public function setUp() {
        $this->doc = new \bolt\dom\document();
        $this->col = new \bolt\dom\collection($this->doc);
    }

    public function testFirstPassthrough() {
        $el = new \bolt\dom\element("div", '', ['id' => 'poop']);
        $this->col->push($el);

        $this->eq("poop", $this->col->getAttribute('id'));
        $this->eq("poop", $this->col->id);

    }

}
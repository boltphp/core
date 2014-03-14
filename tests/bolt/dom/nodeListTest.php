<?php

class dom_nodeListTest extends Test {

    public function setUp() {
        $this->d = new \bolt\dom();
        $this->nl = new \bolt\dom\nodeList($this->d);
    }

    public function test_html() {
        $e1 = $this->d->create('div', '1');
        $e2 = $this->d->create('div', '2');

        $this->eq($this->nl, $this->nl->push($e1));
        $this->eq($this->nl, $this->nl->push($e2));

        $this->eq("<div>1</div>\n<div>2</div>", $this->nl->html());

    }

}
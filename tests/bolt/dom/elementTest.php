<?php

class dom_elementTest extends Test {

    public function setUp() {
        $this->el = new bolt\dom\element("p", "<strong class='t'>poop</strong>");
    }

    public function testDefaultAttribute() {
        $this->eq($this->el->attr('data-domref'), $this->el->refid);
    }

    public function testGetHtml() {
        $this->eq('<strong class="t">poop</strong>', $this->el->html());
    }

    public function testSetHtml() {
    	$html = '<a href="#">poop</a>';
    	$this->el->html($html);
    	$this->eq($html, $this->el->html());
    }

    public function testFindElement() {
    	$a = new dom_elementTest_aref();
    	$this->el->append($a);
    	$ref = $this->el->find("#poop11");
    	$this->eq('<strong class="t">poop</strong><a id="poop11" href="#poop"><b>poop11</b></a>', $this->el->html());
    }

}

class dom_elementTest_aref extends bolt\dom\element {

	public $tagName = 'a';

	public $value = '<b>poop11</b>';

	public $attr = [
		'id' => 'poop11',
		'href' => '#poop'
	];

}
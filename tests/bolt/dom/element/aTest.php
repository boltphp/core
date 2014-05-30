<?php

use bolt\dom\element\a;

class dom_element_aTest extends Test {

	public function setUp() {
		$this->a = new a();
	}

	public function testAttributes() {
		$attr = $this->a->attr;
		$this->eq(true, array_key_exists('href', $attr));
	}

	public function testSetHref() {
		$this->a->href('#poop');
		$this->a->download(true);

		$this->eq('#poop', $this->a->href());

		$this->eq('<a href="#poop" download></a>', $this->a->outerHTML); 
	}

}
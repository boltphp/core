<?php

use bolt\dom;

class domTest extends Test {

	public function setUp() {
		$this->dom = new bolt\dom();
	}

	public function testDocument() {
		$this->assertInstanceOf('bolt\dom\document', $this->dom->document());
	}

	public function testFragment() {
		$this->assertInstanceOf('bolt\dom\fragment', $this->dom->fragment());	
	}

	public function testElement() {
		$this->assertInstanceOf('bolt\dom\element', $this->dom->element('div'));	
	}

	public function testStaticDocument() {
		$this->assertInstanceOf('bolt\dom\document', dom::createDocument());
	}

	public function testStaticFragment() {
		$this->assertInstanceOf('bolt\dom\fragment', dom::createFragment());	
	}

	public function testStaticElement() {
		$this->assertInstanceOf('bolt\dom\element', dom::createElement('div'));	
	}

}
<?php

use bolt\dom\element\script;

class dom_element_scriptTest extends Test {


	public function test() {
		$s = new script();
		$s
			->async()
			->defer()
			->src('http://test.com');

		$this->eq('<script async defer src="http://test.com"></script>', $s->outerHTML);
	}

}
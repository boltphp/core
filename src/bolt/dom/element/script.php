<?php

namespace bolt\dom\element;
use \b;

/**
 * code tag
 */
class script extends \bolt\dom\element {
	
	/**
	 * tag name
	 * 
	 * @var string
	 */
	public $tagName = 'script';

	/**
	 * available attributes
	 * 
	 * @var array
	 */
	public $attr = [
		'async' => null,
		'src' => null,
		'type' => null,
		'defer' => null
	];

	public function async() {
		$this->attr('async', true);
		return $this;
	}

	public function defer() {
		$this->attr('defer', true);
		return $this;
	}

}
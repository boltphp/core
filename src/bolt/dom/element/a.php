<?php

namespace bolt\dom\element;
use \b;

/**
 * anchor tag
 */
class a extends \bolt\dom\element {
	
	/**
	 * tag name
	 * 
	 * @var string
	 */
	public $tagName = 'a';

	/**
	 * available attributes
	 * 
	 * @var array
	 */
	public $attr = [
		'href' => null,
		'target' => null,
		'rel' => null,
		'download' => null,
		'media' => null,
		'ping' => null,
		'hreflang' => null,
		'type' => null
	];

}
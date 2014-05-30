<?php

namespace bolt\dom\element;
use \b;

/**
 * time tag
 */
class time extends \bolt\dom\element {
	
	/**
	 * tag name
	 * 
	 * @var string
	 */
	public $tagName = 'time';

	/**
	 * available attributes
	 * 
	 * @var array
	 */
	public $attr = [
		'datetime' => null
	];

}
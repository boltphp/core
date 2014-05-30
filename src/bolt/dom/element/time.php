<?php

namespace bolt\dom\element;
use \b;

use \DateTime;

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

	public function setDateTime($value) {
		$dt = !is_a($value, 'DateTime') ? new DateTime($value) : $value;
		$this->attr('datetime', $dt->format('c'));
	}

}
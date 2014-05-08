<?php

namespace bolt\render;

/**
 * render interface
 */
interface renderInterface {

	/**
	 * determine whether this renderer 
	 * can compile views
	 *
	 * @return bool
	 */
	public static function canCompile();


	/**
	 * render the provided string
	 *
	 * @param string $str
	 * @param array $vars
	 *
	 * @return string
	 */
	public function render($str, array $vars);

}
<?php
/**
 * Eco is a PHP Framework for PHP 5.5+
 *
 * @package Eco
 * @copyright 2015-2019 Shay Anderson <https://www.shayanderson.com>
 * @license MIT License <https://github.com/shayanderson/eco/blob/master/LICENSE>
 * @link <https://github.com/shayanderson/eco>
 */

use Eco\System;

/**
 * Response helper functions
 */

/**
 * Redirect helper
 *
 * @param string $location
 * @param boolean $use_301
 * @return void
 */
function redirect($location, $use_301 = false)
{
	System::response()->redirect($location, $use_301);
}
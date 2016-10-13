<?php
/**
 * Eco - PHP Micro-Framework (PHP 5.5+)
 *
 * @package Eco
 * @copyright 2015-2016 Shay Anderson <http://www.shayanderson.com>
 * @license MIT License <https://github.com/shayanderson/eco/blob/master/LICENSE>
 * @link <https://github.com/shayanderson/eco>
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
	System::redirect($location, $use_301);
}
<?php
/**
 * Eco is a PHP Framework for PHP 5.5+
 *
 * @package Eco
 * @copyright 2015-2020 Shay Anderson <https://www.shayanderson.com>
 * @license MIT License <https://github.com/shayanderson/eco/blob/master/LICENSE>
 * @link <https://github.com/shayanderson/eco>
 */

use Eco\System;

/**
 * Response helper functions
 */

/**
 * Enable output buffering, call callback, disable output buffering, return contents of output buffer
 *
 * @param callable $callback
 * @param bool $is_clean (deletes current output buffer)
 * @return null|string
 */
function buffer(callable $callback, $is_clean = true)
{
	ob_start();
	$callback();
	return $is_clean ? ob_get_clean() : ob_get_flush();
}

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
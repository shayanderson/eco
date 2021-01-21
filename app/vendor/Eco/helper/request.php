<?php
/**
 * Eco is a PHP Framework for PHP 5.5+
 *
 * @package Eco
 * @copyright 2015-2021 Shay Anderson <https://www.shayanderson.com>
 * @license MIT License <https://github.com/shayanderson/eco/blob/master/LICENSE>
 * @link <https://github.com/shayanderson/eco>
 */

use Eco\System;

/**
 * Request helper functions
 */

/**
 * Request GET variable value getter
 *
 * @param string $key
 * @return mixed
 */
function get($key)
{
	return System::request()->get($key);
}

/**
 * Request GET variable exists flag getter
 *
 * @param string $key
 * @return boolean
 */
function get_has($key)
{
	return System::request()->getHas($key);
}

/**
 * Request GET variable name to callable map
 *
 * @param array $map ([get_var_name => callable, ...])
 * @return void
 */
function get_map(array $map)
{
	foreach($map as $k => $v)
	{
		if(get_has($k))
		{
			$v(get($k));
		}
	}
}

/**
 * Request POST variable value getter
 *
 * @param string $key
 * @return mixed
 */
function post($key)
{
	return System::request()->post($key);
}

/**
 * Request POST variable exists flag getter
 *
 * @param string $key
 * @return boolean
 */
function post_has($key)
{
	return System::request()->postHas($key);
}
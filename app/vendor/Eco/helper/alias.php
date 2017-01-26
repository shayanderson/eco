<?php
/**
 * Eco is a PHP Framework for PHP 5.5+
 *
 * @package Eco
 * @copyright 2015-2017 Shay Anderson <http://www.shayanderson.com>
 * @license MIT License <https://github.com/shayanderson/eco/blob/master/LICENSE>
 * @link <https://github.com/shayanderson/eco>
 */

use Eco\System;
use Eco\System\Keep;

/**
 * Eco core method alias helper functions
 */

/**
 * View breadcrumb helper
 *
 * @param string $title
 * @param string $url
 * @return \Eco\System\Breadcrumb
 */
function breadcrumb($title = null, $url = null)
{
	if(func_num_args())
	{
		System::breadcrumb()->add($title, $url);
	}

	return System::breadcrumb();
}

/**
* Configuration settings file (must return array) to object
*
* @param string $file_path
* @param boolean $store
* @return mixed
*/
function conf($file_path = null, $store = true)
{
	if(!func_num_args()) // getter
	{
		return System::conf();
	}

	return System::conf($file_path, $store);
}

/**
 * Data filter helper
 *
 * @return \Eco\System\Filter
 */
function filter()
{
	return System::filter();
}

/**
 * Session flash helper
 *
 * @param string $key
 * @param mixed $value
 * @return \Eco\System\Session\Flash
 */
function flash($key = null, $value = null)
{
	if(func_num_args() === 1)
	{
		System::flash()->get($key);
	}
	else if(func_num_args() === 2)
	{
		System::flash()->set($key, $value);
	}

	return System::flash();
}

/**
 * Data format helper
 *
 * @return \Eco\System\Format
 */
function format()
{
	return System::format();
}

/**
 * Keep helper
 *
 * @param string $key
 * @param mixed $value
 * @return \Eco\System\Keep
 */
function keep($key = null, $value = null)
{
	if(func_num_args() === 1)
	{
		return Keep::getInstance()->get($key);
	}
	else if(func_num_args() === 2)
	{
		Keep::getInstance()->set($key, $value);
	}

	return Keep::getInstance();
}

/**
 * Map param callback
 *
 * @param string $id
 * @param \callable $callback
 * @return void
 */
function param($id, callable $callback)
{
	System::param($id, $callback);
}

/**
 * Request helper
 *
 * @return \Eco\System\Request
 */
function request()
{
	return System::request();
}

/**
 * Session helper
 *
 * @param string $key
 * @param mixed $value
 * @return \Eco\System\Session
 */
function session($key = null, $value = null)
{
	if(func_num_args() === 1)
	{
		System::session()->get($key);
	}
	else if(func_num_args() === 2)
	{
		System::session()->set($key, $value);
	}

	return System::session();
}

/**
 * Validate helper
 *
 * @return \Eco\System\Validate
 */
function validate()
{
	return System::validate();
}
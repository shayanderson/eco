<?php
/**
 * Eco is a PHP Micro-Framework for PHP 5.5+
 *
 * @package Eco
 * @copyright 2015-2016 Shay Anderson <http://www.shayanderson.com>
 * @license MIT License <https://github.com/shayanderson/eco/blob/master/LICENSE>
 * @link <https://github.com/shayanderson/eco>
 */

use Eco\System;

/**
 * Eco core method alias helper functions
 */

/**
 * View breadcrumb helper
 *
 * @return \Eco\Factory\Breadcrumb
 */
function breadcrumb()
{
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
 * @return \Eco\Factory\Filter
 */
function filter()
{
	return System::filter();
}

/**
 * Session flash helper
 *
 * @return \Eco\Factory\Session\Flash
 */
function flash()
{
	return System::flash();
}

/**
 * Data format helper
 *
 * @return \Eco\Factory\Format
 */
function format()
{
	return System::format();
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
 * @return \Eco\Factory\Request
 */
function request()
{
	return System::request();
}

/**
 * Session helper
 *
 * @return \Eco\Factory\Session
 */
function session()
{
	return System::session();
}

/**
 * Validate helper
 *
 * @return \Eco\Factory\Validate
 */
function validate()
{
	return System::validate();
}
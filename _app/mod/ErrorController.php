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
 * Error controller
 */
class ErrorController
{
	/**
	 * 403 error
	 *
	 * @param mixed $message
	 */
	public function error403()
	{
		echo '<h1>Access Not Allowed</h1>';
		echo '<p>' . eco::errorGetLast() . '</p>';
	}

	/**
	 * 404 error
	 *
	 * @param mixed $message
	 */
	public function error404()
	{
		echo '<h1>Page Not Found</h1>';
		echo '<p>' . eco::errorGetLast() . '</p>';
	}

	/**
	 * 500 error
	 *
	 * @param mixed $message
	 */
	public function error500()
	{
		echo '<h1>Server Error</h1>';
		echo '<p>' . eco::errorGetLast() . '</p>';
	}
}
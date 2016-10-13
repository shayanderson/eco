<?php
/**
 * Eco - PHP Micro-Framework (PHP 5.5+)
 *
 * @package Eco
 * @copyright 2015-2016 Shay Anderson <http://www.shayanderson.com>
 * @license MIT License <https://github.com/shayanderson/eco/blob/master/LICENSE>
 * @link <https://github.com/shayanderson/eco>
 */

use Eco\System;

/**
 * Core helper functions
 */

/**
 * Error handler (ex: 403 Forbidden, 404 Not Found, 500 Internal Server Error)
 *
 * @param string $message
 * @param int $code (ex: 403)
 * @param boolean $http_response_code (set HTTP response code)
 * @return void
 */
function error($message, $code = null, $http_response_code = true)
{
	System::error($message, $code, $http_response_code);
}

/**
 * Add log message
 *
 * @return \Eco\Factory\Log
 */
function logger()
{
	return System::log();
}

/**
 * String/array printer for debugging
 *
 * @var mixed $v
 * @return void
 */
function pa($v = null)
{
	if(count(func_get_args()) > 1)
	{
		foreach(func_get_args() as $arg) pa($arg);
		return;
	}
	echo is_scalar($v) || $v === null ? $v . ( PHP_SAPI === 'cli' ? PHP_EOL : '<br />' )
		: ( PHP_SAPI === 'cli' ? print_r($v, true) : '<pre>' . print_r($v, true) . '</pre>' );
}

/**
 * Session exists flag getter
 *
 * @return boolean
 */
function session_exists()
{
	return session_status() !== PHP_SESSION_NONE;
}

/**
 * Stop application helper
 *
 * @return void
 */
function stop()
{
	System::stop();
}

/**
 * View helper function
 *
 * @param mixed $template
 * @param mixed $view_params (array for params: ['var1' => x, ...])
 * @return \Eco\Factory\View
 */
function view($template = null, $view_params = null)
{
	return System::view($template, $view_params);
}
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
 * Core helper functions
 */

/**
 * Database object getter
 *
 * @param mixed $connection_id
 * @return \Eco\System\Database
 */
function db($connection_id = null)
{
	return System::db($connection_id);
}

/**
 * Error handler (ex: 403 Forbidden, 404 Not Found, 500 Internal Server Error)
 *
 * @param string $message
 * @param int $code (ex: 403)
 * @param string $category
 * @param boolean $http_response_code (set HTTP response code)
 * @return void
 */
function error($message, $code = null, $category = null, $http_response_code = true)
{
	System::error($message, $code, $category, $http_response_code);
}

/**
 * Add log message
 *
 * @return \Eco\System\Log
 */
function logger()
{
	return System::log();
}

/**
 * Model object getter
 *
 * @return \EcoModelRegistry
 */
function model()
{
	return System::model();
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
 * Service object getter
 *
 * @return \EcoServiceRegistry
 */
function service()
{
	return System::service();
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
 * Generate random token
 *
 * @param int $length (length returned in bytes)
 * @return string
 */
function token($length = 32)
{
	$length = (int)$length;
	if($length < 8)
	{
		$length = 8;
	}

	if(!function_exists('random_bytes'))
	{
		return bin2hex(openssl_random_pseudo_bytes($length));
	}

	return bin2hex(random_bytes($length));
}

/**
 * View helper function
 *
 * @param mixed $template
 * @param mixed $view_params (array for params: ['var1' => x, ...])
 * @return \Eco\System\View
 */
function view($template = null, $view_params = null)
{
	return System::view($template, $view_params);
}
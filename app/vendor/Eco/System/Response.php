<?php
/**
 * Eco is a PHP Framework for PHP 5.5+
 *
 * @package Eco
 * @copyright 2015-2019 Shay Anderson <https://www.shayanderson.com>
 * @license MIT License <https://github.com/shayanderson/eco/blob/master/LICENSE>
 * @link <https://github.com/shayanderson/eco>
 */
namespace Eco\System;

use Eco\System;

/**
 * Response helper
 *
 * @author Shay Anderson
 */
class Response extends \Eco\Factory
{
	/**
	 * HTTP response status codes
	 */
	const CODE_CONTINUE = 100;
	const CODE_SWITCHING_PROTOCOLS = 101;
	const CODE_OK = 200;
	const CODE_CREATED = 201;
	const CODE_ACCEPTED = 202;
	const CODE_NON_AUTHORITATIVE_INFORMATION = 203;
	const CODE_NO_CONTENT = 204;
	const CODE_RESET_CONTENT = 205;
	const CODE_PARTIAL_CONTENT = 206;
	const CODE_MULTIPLE_CHOICES = 300;
	const CODE_MOVED_PERMANENTLY = 301;
	const CODE_MOVED_TEMPORARILY = 302;
	const CODE_SEE_OTHER = 303;
	const CODE_NOT_MODIFIED = 304;
	const CODE_USE_PROXY = 305;
	const CODE_BAD_REQUEST = 400;
	const CODE_UNAUTHORIZED = 401;
	const CODE_PAYMENT_REQUIRED = 402;
	const CODE_FORBIDDEN = 403;
	const CODE_NOT_FOUND = 404;
	const CODE_METHOD_NOT_ALLOWED = 405;
	const CODE_NOT_ACCEPTABLE = 406;
	const CODE_PROXY_AUTHENTICATION_REQUIRED = 407;
	const CODE_REQUEST_TIMEOUT = 408;
	const CODE_CONFLICT = 409;
	const CODE_GONE = 410;
	const CODE_LENGTH_REQUIRED = 411;
	const CODE_PRECONDITION_FAILED = 412;
	const CODE_REQUEST_ENTITY_TOO_LARGE = 413;
	const CODE_REQUEST_URI_TOO_LONG = 414;
	const CODE_UNSUPPORTED_MEDIA_TYPE = 415;
	const CODE_INTERNAL_SERVER_ERROR = 500;
	const CODE_NOT_IMPLEMENTED = 501;
	const CODE_BAD_GATEWAY = 502;
	const CODE_SERVICE_UNAVAILABLE = 503;
	const CODE_GATEWAY_TIMEOUT = 504;
	const CODE_VERSION_NOT_SUPPORTED = 505;

	/**
	 * Remove cookie
	 *
	 * @param string $key
	 * @param string $path
	 * @return boolean (true on actual cookie send expired)
	 */
	public function cookieRemove($key, $path = '/')
	{
		if(System::request()->cookieHas($key))
		{
			unset($_COOKIE[$key]);
			return $this->cookieSet($key, null, time() - 3600, $path); // expire cookie to remove
		}

		return false;
	}

	/**
	 * Cookie sender (see more docs at <http://www.php.net/manual/en/function.setcookie.php>)
	 *
	 * @param string $key (ex: 'my_id')
	 * @param mixed $value (cookie value)
	 * @param mixed $expire (string ex: '+30 days', int ex: time() + 3600 (expire in 1 hour))
	 * @param string $path (optional, ex: '/account' (only accessible in /account directory
	 *  + subdirectories))
	 * @param string $domain (optional, ex: 'www.example.com' (accessible in www subdomain + higher))
	 * @param boolean $only_secure (transmit cookie only over HTTPS connection)
	 * @param boolean $http_only (accessible only in HTTP protocol)
	 * @return boolean (false on fail, true on send to client - unknown if client accepts cookie)
	 */
	public function cookieSet($key, $value, $expire = '+1 day', $path = '/', $domain = null,
		$only_secure = false, $http_only = false)
	{
		if(headers_sent())
		{
			return false;
		}

		return setcookie($key, $value, is_string($expire) ? strtotime($expire) : $expire, $path,
			$domain, $only_secure, $http_only);
	}

	/**
	 * HTTP header setter
	 *
	 * @param string $key (ex: "Content-Language")
	 * @param string $value (ex: "en-US")
	 * @return void
	 */
	public function header($key, $value)
	{
		if(!headers_sent())
		{
			header($key . ': ' . $value);
		}
	}

	/**
	 * HTTP headers to prevent page cache
	 *
	 * @return void
	 */
	public function headerNoCache()
	{
		if(!headers_sent())
		{
			header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
			header('Cache-Control: post-check=0, pre-check=0', false);
			header('Pragma: no-cache');
		}
	}

	/**
	 * Remove HTTP header
	 *
	 * @param string $key (ex: "Content-Language")
	 * @return void
	 */
	public function headerRemove($key)
	{
		header_remove($key);
	}

	/**
	 * Data (string|array) to display JSON (with header content-type) and exit application
	 *
	 * @param mixed $data (string|array)
	 * @param mixed $value (optional, use with single key/value)
	 * @return void
	 */
	public function json($data, $value = null)
	{
		if(!headers_sent())
		{
			header('Content-Type: application/json');
		}

		if(func_num_args() === 2) // single key/value
		{
			$data = [$data => $value];
		}

		if(is_object($data))
		{
			$data = (array)$data;
		}

		if(is_array($data))
		{
			$data = json_encode($data);
		}

		echo $data;
		System::stop();
	}

	/**
	* Redirect method
	*
	* @param string $location
	* @param boolean $use_301
	* @return void
	*/
	public function redirect($location, $use_301 = false)
	{
		if(!headers_sent())
		{
			header('Location: ' . $location, true, $use_301 ? 301 : null);
			System::stop();
		}
	}

	/**
	 * HTTP response status code setter
	 *
	 * @param int $code
	 * @return void
	 */
	public function statusCode($code)
	{
		if(!headers_sent())
		{
			\http_response_code($code);
		}
	}
}
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

/**
 * Request helper
 *
 * @author Shay Anderson
 */
class Request extends \Eco\Factory
{
	/**
	 * Input var getter or check if exists
	 *
	 * @staticvar array $vars
	 * @staticvar boolean $is_init
	 * @param string $key
	 * @param bool $is_has
	 * @return mixed
	 */
	private static function __input($key, $is_has = false)
	{
		static $vars = [];
		static $is_init = false;

		if(!$is_init)
		{
			parse_str(file_get_contents('php://input'), $vars);
			$is_init = true;
		}

		if($is_has)
		{
			return isset($vars[$key]);
		}

		return isset($vars[$key]) ? $vars[$key] : null;
	}

	/**
	 * Request cookie value getters
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function cookie($key)
	{
		return $this->cookieHas($key) ? $_COOKIE[$key] : null;
	}

	/**
	 * Request cookie variable exists flag getter
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function cookieHas($key)
	{
		return isset($_COOKIE[$key]);
	}

	/**
	 * Remove cookie
	 *
	 * @param string $key
	 * @param string $path
	 * @return boolean (true on actual cookie send expired)
	 */
	public function cookieRemove($key, $path = '/')
	{
		if($this->cookieHas($key))
		{
			unset($_COOKIE[$key]);
			return $this->cookieSet($key, null, time() - 3600, $path); // expire cookie to remove
		}

		return false;
	}

	/**
	 * Cookie sender (see more docs at <http://www.php.net/manual/en/function.setcookie.php>)
	 *
	 * @param string $name (ex: 'my_id')
	 * @param mixed $value (cookie value)
	 * @param mixed $expire (string ex: '+30 days', int ex: time() + 3600 (expire in 1 hour))
	 * @param string $path (optional, ex: '/account' (only accessible in /account directory + subdirectories))
	 * @param string $domain (optional, ex: 'www.example.com' (accessible in www subdomain + higher))
	 * @param boolean $only_secure (transmit cookie only over HTTPS connection)
	 * @param boolean $http_only (accessible only in HTTP protocol)
	 * @return boolean (false on fail, true on send to client - unknown if client accepts cookie)
	 */
	public function cookieSet($name, $value, $expire = '+1 day', $path = '/', $domain = null,
		$only_secure = false, $http_only = false)
	{
		if(headers_sent())
		{
			return false;
		}

		return setcookie($name, $value, is_string($expire) ? strtotime($expire) : $expire, $path,
			$domain, $only_secure, $http_only);
	}

	/**
	 * Request GET variable value getter
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function get($key)
	{
		return isset($_GET[$key]) ? $_GET[$key] : null;
	}

	/**
	 * Request GET variable exists flag getter
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function getHas($key)
	{
		return isset($_GET[$key]);
	}

	/**
	 * Header value getter
	 *
	 * @param string $name
	 * @return string
	 */
	public function header($name)
	{
		return isset($this->headers()[$name]) ? $this->headers()[$name] : null;
	}

	/**
	 * Headers getter
	 *
	 * @staticvar type $h
	 * @return array
	 */
	public function headers()
	{
		static $h;

		if($h === null)
		{
			if(!function_exists('getallheaders')) // non-Apache fix
			{
				function getallheaders()
				{
					$h = [];
					foreach($_SERVER as $k => $v)
					{
						if(substr($k, 0, 5) === 'HTTP_')
						{
							$h[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ',
								substr($k, 5)))))] = $v;
						}
					}
					return $h;
				}
			}
			$h = getallheaders();
		}

		return $h;
	}

	/**
	 * Request input variable value getter
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function input($key)
	{
		return self::__input($key);
	}

	/**
	 * Request input variable exists flag getter
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function inputHas($key)
	{
		return self::__input($key, true);
	}

	/**
	 * Request IP address getter
	 *
	 * @return string
	 */
	public function ipAddress()
	{
		return $this->server('REMOTE_ADDR');
	}

	/**
	 * Post request method flag getter
	 *
	 * @return boolean
	 */
	public function isPost()
	{
		return strtoupper($this->server('REQUEST_METHOD')) === 'POST';
	}

	/**
	 * Secure (HTTPS) request flag getter
	 *
	 * @return boolean
	 */
	public function isSecure()
	{
		return strtoupper($this->server('HTTPS')) === 'ON';
	}

	/**
	 * Request method getter
	 *
	 * @return string (ex: "PUT")
	 */
	public function method()
	{
		return $this->server('REQUEST_METHOD');
	}

	/**
	 * Request POST variable value getter
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function post($key)
	{
		return isset($_POST[$key]) ? $_POST[$key] : null;
	}

	/**
	 * Request POST variable exists flag getter
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function postHas($key)
	{
		return isset($_POST[$key]);
	}

	/**
	 * Request server value getter
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function server($key)
	{
		return isset($_SERVER[$key]) ? $_SERVER[$key] : null;
	}

	/**
	 * Request server variable exists flag getter
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function serverHas($key)
	{
		return isset($_SERVER[$key]);
	}

	/**
	 * Request URI getter
	 *
	 * @param boolean $query_string (include query string with URI)
	 * @return string
	 */
	public function uri($query_string = true)
	{
		$uri = $this->server('REQUEST_URI');

		if($query_string)
		{
			return $uri;
		}

		return ($pos = strpos($uri, '?')) !== false ? substr($uri, 0, $pos) : $uri;
	}
}
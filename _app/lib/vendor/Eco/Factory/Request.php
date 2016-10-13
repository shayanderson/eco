<?php
/**
 * Eco - PHP Micro-Framework (PHP 5.5+)
 *
 * @package Eco
 * @copyright 2015-2016 Shay Anderson <http://www.shayanderson.com>
 * @license MIT License <https://github.com/shayanderson/eco/blob/master/LICENSE>
 * @link <https://github.com/shayanderson/eco>
 */
namespace Eco\Factory;

/**
 * Request helper
 *
 * @author Shay Anderson
 */
class Request extends \Eco\Factory
{
	/**
	 * Request cookie value getters
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function cookie($key)
	{
		return $this->cookie_has($key) ? $_COOKIE[$key] : null;
	}

	/**
	 * Request cookie variable exists flag getter
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function cookie_has($key)
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
	public function cookie_remove($key, $path = '/')
	{
		if($this->cookie_has($key))
		{
			unset($_COOKIE[$key]);
			return $this->cookie_set($key, null, time() - 3600, $path); // expire cookie to remove
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
	public function cookie_set($name, $value, $expire = '+1 day', $path = '/', $domain = null,
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
	public function get_has($key)
	{
		return isset($_GET[$key]);
	}

	/**
	 * Request IP address getter
	 *
	 * @return string
	 */
	public function get_request_ip_address()
	{
		return $this->request_server('REMOTE_ADDR');
	}

	/**
	 * Request URI getter
	 *
	 * @param boolean $query_string (include query string with URI)
	 * @return string
	 */
	public function get_request_uri($query_string = true)
	{
		$uri = $this->request_server('REQUEST_URI');

		if($query_string)
		{
			return $uri;
		}

		return ($pos = strpos($uri, '?')) !== false ? substr($uri, 0, $pos) : $uri;
	}

	/**
	 * Post request method flag getter
	 *
	 * @return boolean
	 */
	public function is_request_post()
	{
		return strtoupper($this->request_server('REQUEST_METHOD')) === 'POST';
	}

	/**
	 * Secure (HTTPS) request flag getter
	 *
	 * @return boolean
	 */
	public function is_request_secure()
	{
		return strtoupper($this->request_server('HTTPS')) === 'ON';
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
	public function post_has($key)
	{
		return isset($_POST[$key]);
	}

	/**
	 * Request server value getter
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function request_server($key)
	{
		return isset($_SERVER[$key]) ? $_SERVER[$key] : null;
	}

	/**
	 * Request server variable exists flag getter
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function request_server_has($key)
	{
		return isset($_SERVER[$key]);
	}
}
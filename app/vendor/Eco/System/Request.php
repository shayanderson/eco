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
	 * @param bool $auto_trim
	 * @return string
	 */
	public function header($name, $auto_trim = true)
	{
		return isset($this->headers()[$name])
			? ( $auto_trim ? trim($this->headers()[$name]) : $this->headers()[$name] ) : '';
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
	 * Input data getter
	 *
	 * @param bool $convert_html_entities
	 * @return string
	 */
	public function input($convert_html_entities = true)
	{
		if($convert_html_entities)
		{
			return html_entity_decode(file_get_contents('php://input'));
		}

		return file_get_contents('php://input');
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
<?php
/**
 * Eco is a PHP Framework for PHP 5.5+
 *
 * @package Eco
 * @copyright 2015-2021 Shay Anderson <https://www.shayanderson.com>
 * @license MIT License <https://github.com/shayanderson/eco/blob/master/LICENSE>
 * @link <https://github.com/shayanderson/eco>
 */
namespace Eco;

/**
 * HTTP request
 *
 * @author Shay Anderson
 */
class Http
{
	/**
	 * Request types
	 */
	const TYPE_DELETE = 1;
	const TYPE_GET = 2;
	const TYPE_HEAD = 3;
	const TYPE_PATCH = 4;
	const TYPE_POST = 5;
	const TYPE_PUT = 6;

	/**
	 * cURL callback
	 *
	 * @var callable
	 */
	private $__curl_callback;

	/**
	 * Last error message
	 *
	 * @var string
	 */
	private $__error;

	/**
	 * Last error number
	 *
	 * @var int
	 */
	private $__error_num;

	/**
	 * Error flag
	 *
	 * @var boolean
	 */
	private $__is_error = false;

	/**
	 * Request parameters
	 *
	 * @var array
	 */
	private $__param = [];

	/**
	 * Port
	 *
	 * @var int
	 */
	private $__port;

	/**
	 * HTTP response code
	 *
	 * @var int
	 */
	private $__response_code;

	/**
	 * Request URL
	 *
	 * @var string
	 */
	private $__url;

	/**
	 * Certificate file path (used with verify peer)
	 *
	 * @var string
	 */
	public $cert_file_path;

	/**
	 * Cookie contents
	 *
	 * @var string
	 */
	public $cookie;

	/**
	 * Cookie file path
	 *
	 * @var string
	 */
	public $cookie_file_path;

	/**
	 * Cookie jar path
	 *
	 * @var string
	 */
	public $cookie_jar_path;

	/**
	 * Force TLS v1.2 connection
	 *
	 * @var boolean
	 */
	public $force_tls_v1_2 = false;

	/**
	 * Add headers (ex: ['Accept-Language: en-US', 'Accept-Encoding: gzip, deflate'])
	 *
	 * @var array
	 */
	public $headers = [];

	/**
	 * Include headers in response
	 *
	 * @var boolean
	 */
	public $headers_get = false;

	/**
	 * Proxy server IP address and port (ex: '1.2.3.4:8080')
	 *
	 * @var string
	 */
	public $proxy;

	/**
	 * Ignore request redirects
	 *
	 * @var boolean
	 */
	public $redirects_ignore = false;

	/**
	 * Request referer
	 *
	 * @var string
	 */
	public $referer;

	/**
	 * Max seconds to allow cURL functions to execute
	 *
	 * @var int
	 */
	public $timeout = 10;

	/**
	 * Seconds to wait while trying to connect (use 0 to wait indefinitely)
	 *
	 * @var int
	 */
	public $timeout_connection = 10;

	/**
	 * Request user agent
	 *
	 * @var string
	 */
	public $user_agent;

	/**
	 * Verify peers certificate
	 *
	 * @var boolean
	 */
	public $verify_peer = true;

	/**
	 * Init
	 *
	 * @param string $url
	 * @param int|null $port
	 * @param callable|null $curl_callback
	 */
	public function __construct($url, $port = null, $curl_callback = null)
	{
		$this->__url = $url;
		$this->__port = $port;
		$this->__curl_callback = $curl_callback;
	}

	/**
	 * Send request
	 *
	 * @param int $type
	 * @param mixed $params
	 * @return mixed
	 */
	private function __fetch($type, $params)
	{
		if(is_array($params))
		{
			$params += $this->__param;
		}

		// get
		if($type === self::TYPE_GET && $params)
		{
			if(strpos($this->__url, '?') === false) // add query string
			{
				$this->__url .= '?' . http_build_query($params);
			}
			else // append query string
			{
				$this->__url .= '&' . http_build_query($params);
			}
		}

		$ch = curl_init($this->__url);

		if((int)$this->__port)
		{
			curl_setopt($ch, CURLOPT_PORT, (int)$this->__port);
		}

		// global
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout_connection);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		if($this->headers_get)
		{
			curl_setopt($ch, CURLOPT_HEADER, true);
		}

		if(!$this->redirects_ignore)
		{
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		}

		if($this->force_tls_v1_2)
		{
			curl_setopt($ch, CURLOPT_SSLVERSION, 6); // force TLS v1.2
		}

		if($this->verify_peer && $this->cert_file_path)
		{
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
			curl_setopt($ch, CURLOPT_CAINFO, $this->cert_file_path);
		}
		else
		{
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		}

		if($this->cookie)
		{
			curl_setopt($ch, CURLOPT_COOKIE, $this->cookie);
		}

		if($this->cookie_file_path)
		{
			curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file_path);
		}

		if($this->cookie_jar_path)
		{
			curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_jar_path);
		}

		if($this->proxy)
		{
			curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
		}

		if($this->referer)
		{
			curl_setopt($ch, CURLOPT_REFERER, $this->referer);
		}

		if($this->user_agent)
		{
			curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
		}

		if($type === self::TYPE_POST) // post
		{
			curl_setopt($ch, CURLOPT_POST, true);
		}
		else if($type === self::TYPE_DELETE) // delete
		{
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
		}
		else if($type === self::TYPE_HEAD) // head
		{
			curl_setopt($ch, CURLOPT_NOBODY, true);
		}
		else if($type === self::TYPE_PATCH) // patch
		{
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
		}
		else if($type === self::TYPE_PUT)
		{
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
		}

		if($params && ( $type === self::TYPE_POST || $type === self::TYPE_DELETE
			|| $type === self::TYPE_PATCH || $type === self::TYPE_PUT ))
		{
			if(is_array($params) || is_object($params))
			{
				$params = http_build_query($params);
			}

			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		}

		if($this->headers && is_array($this->headers))
		{
			curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
		}

		if($this->__curl_callback)
		{
			$f = $this->__curl_callback;
			$f($ch);
		}

		$response = curl_exec($ch);

		$this->__response_code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if($response === false)
		{
			$this->__is_error = true;
			$this->__error = curl_error($ch);
			$this->__error_num = (int)curl_errno($ch);
		}

		curl_close($ch);

		return $response;
	}

	/**
	 * DELETE request
	 *
	 * @param mixed $params
	 * @return mixed (string or false on error)
	 */
	public function delete($params = null)
	{
		return $this->__fetch(self::TYPE_DELETE, $params);
	}

	/**
	 * GET request
	 *
	 * @param array $params
	 * @return mixed (string or false on error)
	 */
	public function get(array $params = null)
	{
		return $this->__fetch(self::TYPE_GET, $params);
	}

	/**
	 * Last error message getter
	 *
	 * @return string
	 */
	public function getError()
	{
		return $this->__error;
	}

	/**
	 * Last error number getter
	 *
	 * @return int
	 */
	public function getErrorNumber()
	{
		return $this->__error_num;
	}

	/**
	 * HTTP response code getter
	 *
	 * @return int
	 */
	public function getResponseCode()
	{
		return $this->__response_code;
	}

	/**
	 * Request URL getter
	 *
	 * @return string
	 */
	public function getUrl()
	{
		return $this->__url;
	}

	/**
	 * HEAD request
	 *
	 * @return boolean (true or false on error)
	 */
	public function head()
	{
		return $this->__fetch(self::TYPE_HEAD, null) !== false;
	}

	/**
	 * Convert headers string to array
	 *
	 * @param string $response_headers
	 * @return array
	 */
	public function headersStringToArray($response_headers)
	{
		$a = [];

		$response_headers = substr($response_headers, 0, strpos($response_headers, "\r\n\r\n"));

		foreach(explode("\r\n", $response_headers) as $k => $v)
		{
			if(!$k)
			{
				$a['status_line'] = $v;
			}
			else
			{
				$v = explode(':', $v);
				if(isset($v[0], $v[1]))
				{
					$a[$v[0]] = $v[1];
				}
			}
		}

		return $a;
	}

	/**
	 * Connection error occurred flag getter
	 *
	 * @return boolean
	 */
	public function isError()
	{
		return $this->__is_error;
	}

	/**
	 * Request param setter
	 *
	 * @param string $id
	 * @param mixed $value
	 * @return void
	 */
	public function param($id, $value)
	{
		$this->__param[$id] = $value;
	}

	/**
	 * PATCH request
	 *
	 * @param mixed $params
	 * @return mixed (string or false on error)
	 */
	public function patch($params = null)
	{
		return $this->__fetch(self::TYPE_PATCH, $params);
	}

	/**
	 * POST request
	 *
	 * @param mixed $params
	 * @return mixed (string or false on error)
	 */
	public function post($params = null)
	{
		return $this->__fetch(self::TYPE_POST, $params);
	}

	/**
	 * PUT request
	 *
	 * @param mixed $params
	 * @return mixed (string or false on error)
	 */
	public function put($params = null)
	{
		return $this->__fetch(self::TYPE_PUT, $params);
	}
}
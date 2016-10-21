<?php
/**
 * Eco is a PHP Framework for PHP 5.5+
 *
 * @package Eco
 * @copyright 2015-2016 Shay Anderson <http://www.shayanderson.com>
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
	const TYPE_GET = 1;
	const TYPE_HEAD = 2;
	const TYPE_POST = 3;

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
	 */
	public function __construct($url)
	{
		$this->__url = $url;
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
		else
		{
			$params = $this->__param;
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

		// head
		if($type === self::TYPE_HEAD)
		{
			curl_setopt($ch, CURLOPT_NOBODY, true);
		}

		// post
		if($type === self::TYPE_POST && count($params))
		{
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
		}

		if($this->headers && is_array($this->headers))
		{
			curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
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
	 * @param array $params
	 * @return boolean (true or false on error)
	 */
	public function head(array $params = null)
	{
		return $this->__fetch(self::TYPE_HEAD, $params) !== false;
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
	 * POST request
	 *
	 * @param array $params
	 * @return mixed (string or false on error)
	 */
	public function post(array $params = null)
	{
		return $this->__fetch(self::TYPE_POST, $params);
	}
}
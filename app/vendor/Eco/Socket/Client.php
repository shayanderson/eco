<?php
/**
 * Eco is a PHP Framework for PHP 5.5+
 *
 * @package Eco
 * @copyright 2015-2019 Shay Anderson <https://www.shayanderson.com>
 * @license MIT License <https://github.com/shayanderson/eco/blob/master/LICENSE>
 * @link <https://github.com/shayanderson/eco>
 */
namespace Eco\Socket;

/**
 * Client socket
 *
 * @author Shay Anderson
 */
class Client extends \Eco\Socket\Stream
{
	/**
	 * Timeout (seconds)
	 *
	 * @var float
	 */
	private $timeout;

	/**
	 * Init
	 *
	 * @param string $ip_address
	 * @param int $port
	 * @param float $timeout
	 * @param string $type
	 */
	public function __construct($ip_address, $port, $timeout = 10, $type = self::TYPE_TCP)
	{
		$this->timeout = $timeout;
		parent::__construct($ip_address, $port, $type);
	}

	/**
	 * Create socket
	 *
	 * @return void
	 * @throws \Exception
	 */
	protected function create()
	{
		$this->socket = @stream_socket_client($this->endpoint, $errno, $errstr, $this->timeout);

		if($this->socket === false || $errno)
		{
			throw new \Exception(__METHOD__ . ': socket connection "' . $this->endpoint
				. '" failed: ' . $errstr . ' (' . $errno . ')');
		}

		stream_set_blocking($this->socket, false);
	}

	/**
	 * Read/get data from server
	 *
	 * @return string|bool (false on no data)
	 */
	public function read()
	{
		$this->connect();

		$data = '';

		while(!feof($this->socket))
		{
			$data .= fgets($this->socket, 1024);
		}

		return strlen($data) ? $data : false;
	}

	/**
	 * Write/set server data
	 *
	 * @param string $data
	 * @return string|bool (false on no data)
	 */
	public function write($data)
	{
		$this->connect();

		if(stream_socket_sendto($this->socket, $data))
		{
			return $this->read();
		}

		return false;
	}
}
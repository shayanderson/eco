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
 * Stream socket
 *
 * @author Shay Anderson
 */
abstract class Stream
{
	/**
	 * Socket transports
	 */
	const TYPE_TCP = 'tcp';
	const TYPE_UDP = 'udp';
	// const TYPE_UNIX = 'unix';

	/**
	 * Endpoint
	 *
	 * @var string
	 */
	protected $endpoint;

	/**
	 * Socket
	 *
	 * @var resource
	 */
	protected $socket;

	/**
	 * Init
	 *
	 * @param string $ip_address
	 * @param int $port
	 * @param string $type (Note: for server use tcp for ssl|tls connections
	 *		only the client should use ssl|tls)
	 */
	public function __construct($ip_address, $port, $type = self::TYPE_TCP)
	{
		$this->endpoint = $type . '://' . $ip_address . ( $port ? ':' . $port : null );
	}

	/**
	 * Destruct
	 */
	public function __destruct()
	{
		$this->disconnect();
	}

	/**
	 * Connect
	 *
	 * @return void
	 */
	protected function connect()
	{
		if(!$this->socket)
		{
			$this->create();
		}
	}

	/**
	 * Create socket
	 *
	 * @return void
	 */
	abstract protected function create();

	/**
	 * Disconnect
	 *
	 * @return void
	 */
	public function disconnect()
	{
		if($this->socket)
		{
			stream_socket_shutdown($this->socket, STREAM_SHUT_RDWR);
			stream_set_blocking($this->socket, false);
			fclose($this->socket);
		}

		$this->socket = null;
	}
}
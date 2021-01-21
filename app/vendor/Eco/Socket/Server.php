<?php
/**
 * Eco is a PHP Framework for PHP 5.5+
 *
 * @package Eco
 * @copyright 2015-2021 Shay Anderson <https://www.shayanderson.com>
 * @license MIT License <https://github.com/shayanderson/eco/blob/master/LICENSE>
 * @link <https://github.com/shayanderson/eco>
 */
namespace Eco\Socket;

/**
 * Server socket
 *
 * @author Shay Anderson
 */
class Server extends \Eco\Socket\Stream
{
	/**
	 * Create socket
	 *
	 * @return void
	 * @throws \Exception
	 */
	protected function create()
	{
		$this->socket = @stream_socket_server($this->endpoint, $errno, $errstr);

		if($this->socket === false || $errno)
		{
			throw new \Exception(__METHOD__ . ': failed to bind to socket "' . $this->endpoint
				. '": ' . $errstr . ' (' . $errno . ')');
		}

		set_time_limit(0);
		ob_implicit_flush();
	}

	/**
	 * Listener
	 *
	 * @param callable $callback
	 * @param bool $is_read
	 */
	protected function listener(callable $callback, $is_read = true)
	{
		$this->connect();

		while(true)
		{
			while($client = @stream_socket_accept($this->socket, 10))
			{
				if($is_read)
				{
					$req = fread($client, 999999); // improve
					$res = $callback($req);
				}
				else
				{
					$res = $callback();
				}

				if($res)
				{
					stream_socket_sendto($client, $res);
				}

				fclose($client);
			}
		}
	}

	/**
	 * Write/set data response
	 *
	 * @param bool|callable $read_callback
	 * @param callable $callback
	 * @return void
	 */
	public function listen($read_callback, callable $callback = null)
	{
		$argn = func_num_args();
		$this->listener($argn === 2 ? $callback : $read_callback, $argn === 2 && $read_callback);
	}
}
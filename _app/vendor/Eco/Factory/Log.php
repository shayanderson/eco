<?php
/**
 * Eco is a PHP Framework for PHP 5.5+
 *
 * @package Eco
 * @copyright 2015-2016 Shay Anderson <http://www.shayanderson.com>
 * @license MIT License <https://github.com/shayanderson/eco/blob/master/LICENSE>
 * @link <https://github.com/shayanderson/eco>
 */
namespace Eco\Factory;

use Eco\System;

/**
 * Logging helper
 *
 * @author Shay Anderson
 */
class Log extends \Eco\Factory
{
	/**
	 * Log levels
	 */
	const LOG_DEBUG = 4;
	const LOG_ERROR = 1;
	const LOG_NONE = 5;
	const LOG_NOTICE = 3;
	const LOG_WARNING = 2;

	/**
	 * Log handler
	 *
	 * @var \callable
	 */
	private $__handler;

	/**
	 * Log entry ID
	 *
	 * @var int
	 */
	private $__id = 0;

	/**
	 * Log
	 *
	 * @var array
	 */
	private $__log = [];

	/**
	 * Add log entry
	 *
	 * @param string $message
	 * @param string $category
	 * @param int $level
	 * @param mixed $info
	 * @return void
	 */
	private function __add($message, $category, $level, $info)
	{
		if($level < self::LOG_NONE && $level <= System::conf()->__eco__->log->level)
		{
			if($this->__handler !== null) // log handler
			{
				$handler = $this->__handler;
				if($handler($message, $level, $category, $info) !== true)
				{
					return; // handled
				}
			}

			$this->__id++;
			$this->__log[$this->__id] = [
				'message' => $message,
				'category' => $category,
				'level' => $level
			];

			if($info !== null)
			{
				$this->__log[$this->__id]['info'] = $info;
			}
		}
	}

	/**
	 * Debug message
	 *
	 * @param string $message
	 * @param mixed $category (string or null)
	 * @param mixed $info (array or null)
	 * @return void
	 */
	public function debug($message, $category = null, $info = null)
	{
		$this->__add($message, $category, self::LOG_DEBUG, $info);
	}

	/**
	 * Error message
	 *
	 * @param string $message
	 * @param mixed $category (string or null)
	 * @param mixed $info (array or null)
	 * @return void
	 */
	public function error($message, $category = null, $info = null)
	{
		$this->__add($message, $category, self::LOG_ERROR, $info);
	}

	/**
	 * Log getter
	 *
	 * @return array
	 */
	public function get()
	{
		return $this->__log;
	}

	/**
	 * Notice message
	 *
	 * @param string $message
	 * @param mixed $category (string or null)
	 * @param mixed $info (array or null)
	 * @return void
	 */
	public function notice($message, $category = null, $info = null)
	{
		$this->__add($message, $category, self::LOG_NOTICE, $info);
	}

	/**
	 * Log handler setter
	 *
	 * @param \callable $handler
	 * @return void
	 */
	public function setHandler(callable $handler)
	{
		$this->__handler = $handler;
	}

	/**
	 * Warning message
	 *
	 * @param string $message
	 * @param mixed $category (string or null)
	 * @param mixed $info (array or null)
	 * @return void
	 */
	public function warning($message, $category = null, $info = null)
	{
		$this->__add($message, $category, self::LOG_WARNING, $info);
	}
}
<?php
/**
 * Eco is a PHP Framework for PHP 5.5+
 *
 * @package Eco
 * @copyright 2015-2019 Shay Anderson <https://www.shayanderson.com>
 * @license MIT License <https://github.com/shayanderson/eco/blob/master/LICENSE>
 * @link <https://github.com/shayanderson/eco>
 */
namespace Eco;

/**
 * Base
 *
 * @author Shay Anderson
 *
 * @property \Eco\System\Collection $list
 */
abstract class Base
{
	/**
	 * Error handler
	 *
	 * @var callable
	 */
	private static $handler_error;

	/**
	 * Log handler
	 *
	 * @var callable
	 */
	private static $handler_log;

	/**
	 * Lists
	 *
	 * @var array
	 */
	private $lists = [];

	/**
	 * List prop handler
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name)
	{
		if($name === 'list')
		{
			return $this->list();
		}
	}

	/**
	 * Error
	 *
	 * @param string $message
	 * @param int|null $code
	 * @param string $category
	 * @return void
	 * @throws \Exception (on error handler does not exist)
	 */
	protected function error($message, $code = System::ERROR_SERVER, $category = null)
	{
		if(!self::$handler_error)
		{
			throw new \Exception('Error handler does not exist (' . __METHOD__ . ')');
		}

		$cb = self::$handler_error;
		$cb($message, $code, $category);
	}

	/**
	 * List getter
	 *
	 * @param type $name
	 * @return \Eco\System\Collection
	 */
	protected function &list($name = null)
	{
		if(!isset($this->lists[$name]))
		{
			$this->lists[$name] = new System\Collection;
		}

		return $this->lists[$name];
	}

	/**
	 * Log
	 *
	 * @param string $message
	 * @param mixed $level
	 * @param null|string $category
	 * @param null|string $info
	 * @return void
	 */
	protected function log($message, $level = null, $category = null, $info = null)
	{
		if(!self::$handler_log)
		{
			throw new \Exception('Log handler does not exist (' . __METHOD__ . ')');
		}

		$cb = self::$handler_log;
		$cb($message, $level, $category, $info);
	}

	/**
	 * Property map
	 *
	 * @param array $map ([name => value])
	 * @param array $required ([name] OR [name => cast-type], ex: [name=>int])
	 * @param array $rename ([key-name => prop-name])
	 * @return void
	 * @throws \Exception (on prop missing)
	 */
	protected function propMap(array $map, array $required = null, array $rename = null)
	{
		$type = [];

		if($required)
		{
			foreach($required as $k => $v)
			{
				if(!is_numeric($k))
				{
					$type[$k] = $v;
					$v = $k;
				}

				if(!isset($map[$v]) || !array_key_exists($v, $map))
				{
					throw new \Exception('Required property "' . $v . '" does not exist in map ('
						. __METHOD__ . ')');
				}
			}
		}

		foreach($map as $k => $v)
		{
			if(isset($rename[$k]))
			{
				$k = $rename[$k];
			}

			if(!property_exists($this, $k))
			{
				throw new \Exception('Property "' . $k . '" does not exist (' . __METHOD__ . ')');
			}

			if(isset($type[$k])) // type cast
			{
				switch($type[$k])
				{
					case 'bool':
					case 'boolean':
						$v = (bool)$v;
						break;

					case 'float':
						$v = (float)$v;
						break;

					case 'int':
						$v = (int)$v;
						break;

					case 'string':
						$v = (string)$v;
						break;
				}
			}

			$this->{$k} = $v;
		}
	}

	/**
	 * Error handler setter
	 *
	 * @param callable $handler (message, code, category)
	 * @return void
	 */
	public static function setErrorHandler(callable $handler)
	{
		self::$handler_error = $handler;
	}

	/**
	 * Log handler setter
	 *
	 * @param callable $handler (message, level, category, info)
	 * @return void
	 */
	public static function setLogHandler(callable $handler)
	{
		self::$handler_log = $handler;
	}
}
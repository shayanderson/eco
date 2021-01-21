<?php
/**
 * Eco is a PHP Framework for PHP 5.5+
 *
 * @package Eco
 * @copyright 2015-2021 Shay Anderson <https://www.shayanderson.com>
 * @license MIT License <https://github.com/shayanderson/eco/blob/master/LICENSE>
 * @link <https://github.com/shayanderson/eco>
 */
namespace Eco\System;

/**
 * Collection
 *
 * @author Shay Anderson
 */
class Collection
{
	/**
	 * Map
	 *
	 * @var array
	 */
	private $map = [];

	/**
	 * Enforce keys must exist
	 *
	 * @var bool
	 */
	public $exception_on_missing_key = true;

	/**
	 * Clear key/value
	 *
	 * @param string $key
	 * @return void
	 */
	public function clear($key)
	{
		if($this->has($key))
		{
			unset($this->map[$key]);
		}
	}

	/**
	 * Array item count getter
	 *
	 * @return int
	 */
	public function count()
	{
		return count($this->map);
	}

	/**
	 * Value by key getter
	 *
	 * @param string $key
	 * @return mixed
	 * @throws \Exception (on key does not exist when enforcing keys must exist)
	 */
	public function get($key)
	{
		if($this->has($key))
		{
			return $this->map[$key];
		}

		if($this->exception_on_missing_key)
		{
			throw new \Exception('Key "' . $key . '" does not exist in collection ('
				. __METHOD__ . ')');
		}

		return null;
	}

	/**
	 * Array getter
	 *
	 * @return array
	 */
	public function getArray()
	{
		return $this->map;
	}

	/**
	 * Check if key exists
	 *
	 * @param string $key
	 * @return bool
	 */
	public function has($key)
	{
		return isset($this->map[$key]) || array_key_exists($key, $this->map);
	}

	/**
	 * Reset array to empty
	 *
	 * @return void
	 */
	public function reset()
	{
		$this->map = [];
	}

	/**
	 * Set key/value or array of keys/values
	 *
	 * @param mixed $key_or_array
	 * @param mixed $value
	 * @return void
	 */
	public function set($key_or_array, $value = null)
	{
		if(is_array($key_or_array))
		{
			foreach($key_or_array as $k => $v)
			{
				$this->set($k, $v);
			}

			return;
		}

		$this->map[$key_or_array] = $value;
	}
}
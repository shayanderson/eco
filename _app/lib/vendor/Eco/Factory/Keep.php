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
 * Global registry key/value store
 *
 * @author Shay Anderson
 */
class Keep extends \Eco\Factory
{
	/**
	 * Store
	 *
	 * @var array
	 */
	private $__store = [];

	/**
	 * Delete key
	 *
	 * @param string $key
	 * @return void
	 */
	public function clear($key)
	{
		unset($this->__store[$key]);
	}

	/**
	 * Value getter
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function get($key)
	{
		return $this->has($key) ? $this->__store[$key] : null;
	}

	/**
	 * Get store
	 *
	 * @return array
	 */
	public function getAll()
	{
		return $this->__store;
	}

	/**
	 * Key exists flag getter
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function has($key)
	{
		return isset($this->__store[$key]);
	}

	/**
	 * Key/value setter
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public function set($key, $value = null)
	{
		if(is_array($key))
		{
			foreach($key as $k => $v)
			{
				$this->set($k, $v);
			}

			return;
		}

		$this->__store[$key] = $value;
	}
}
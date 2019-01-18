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

use Eco\System;

/**
 * Registry
 *
 * @author Shay Anderson
 */
class Registry extends \Eco\Factory
{
	/**
	 * Registry child ID
	 */
	const ID = null;

	/**
	 * Class registry
	 *
	 * @var array
	 */
	private static $__registry;

	/**
	 * Class getter
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name)
	{
		static $reg = [];

		if(isset($reg[static::ID][$name]))
		{
			return $reg[static::ID][$name];
		}

		if(isset(self::$__registry[static::ID][$name])) // lazy load
		{
			if(!class_exists(self::$__registry[static::ID][$name]))
			{
				System::error(__METHOD__ . ': failed to find ' . static::ID . ' class \''
					. self::$__registry[static::ID][$name] . '\'', null, 'Eco');
			}

			$class = self::$__registry[static::ID][$name];

			$reg[static::ID][$name] = new $class;

			return $reg[static::ID][$name];
		}

		System::error(__METHOD__ . ': failed to find ' . static::ID . ' \'' . $name . '\'', null,
			'Eco');
	}

	/**
	 * Initialize registry
	 *
	 * @param array $registry
	 */
	public function __init(array &$registry)
	{
		if(!isset(self::$__registry[static::ID]))
		{
			self::$__registry[static::ID] = &$registry;
		}
	}

	/**
	 * Registry getter
	 *
	 * @return array
	 */
	public function get()
	{
		return self::$__registry[static::ID];
	}
}
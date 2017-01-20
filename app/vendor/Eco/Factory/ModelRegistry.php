<?php
/**
 * Eco is a PHP Framework for PHP 5.5+
 *
 * @package Eco
 * @copyright 2015-2017 Shay Anderson <http://www.shayanderson.com>
 * @license MIT License <https://github.com/shayanderson/eco/blob/master/LICENSE>
 * @link <https://github.com/shayanderson/eco>
 */
namespace Eco\Factory;

/**
 * Model registry
 *
 * @author Shay Anderson
 */
abstract class ModelRegistry extends \Eco\Factory
{
	/**
	 * Model registry
	 *
	 * @var array
	 */
	private static $__registry;

	/**
	 * Model getter
	 *
	 * @param string $name
	 * @return mixed
	 * @throws \Exception (model or model class does not exist)
	 */
	public function __get($name)
	{
		static $model = [];

		if(isset($model[$name]))
		{
			return $model[$name];
		}

		if(isset(self::$__registry[$name])) // lazy load
		{
			if(!class_exists(self::$__registry[$name]))
			{
				throw new \Exception('Failed to find model class \'' . self::$__registry[$name]
					. '\' (' . __METHOD__ . ')');
			}

			$model[$name] = new self::$__registry[$name];
			return $model[$name];
		}

		throw new \Exception('Failed to find model \'' . $name . '\' (' . __METHOD__ . ')');
	}

	/**
	 * Initialize registry
	 *
	 * @param array $registry
	 */
	public function __init(array &$registry)
	{
		if(!self::$__registry)
		{
			self::$__registry = &$registry;
		}
	}

	/**
	 * Registry getter
	 *
	 * @return array
	 */
	public function get()
	{
		return self::$__registry;
	}
}
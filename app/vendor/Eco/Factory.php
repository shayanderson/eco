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
 * Factory
 *
 * @author Shay Anderson
 */
abstract class Factory
{
	/**
	 * Instances
	 *
	 * @var array
	 */
	private static $__instances = [];

	/**
	 * Protected
	 */
	protected function __construct() {}

	/**
	 * Not allowed
	 */
	private function __clone() {}

	/**
	 * Not allowed
	 */
	private function __wakeup() {}

	/**
	 * Instance getter
	 *
	 * @return \Eco\Factory
	 */
	public static function getInstance()
	{
		$class = get_called_class();

		if(!isset(self::$__instances[$class]))
		{
			self::$__instances[$class] = new $class;
		}

		return self::$__instances[$class];
	}
}
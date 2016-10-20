<?php
/**
 * Eco is a PHP Micro-Framework for PHP 5.5+
 *
 * @package Eco
 * @copyright 2015-2016 Shay Anderson <http://www.shayanderson.com>
 * @license MIT License <https://github.com/shayanderson/eco/blob/master/LICENSE>
 * @link <https://github.com/shayanderson/eco>
 */
namespace Eco\Factory;

use Eco\System;

/**
 * View class
 *
 * @author Shay Anderson
 */
class View extends \Eco\Factory
{
	/**
	 * Params
	 *
	 * @var array
	 */
	private $__param = [];

	/**
	 * Param prop getter
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function __get($key)
	{
		return $this->get($key);
	}

	/**
	 * Param prop setter
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public function __set($key, $value)
	{
		$this->set($key, $value);
	}

	/**
	 * Display template file
	 *
	 * @param string $template
	 * @return void
	 */
	public function display($template)
	{
		// format template name
		$template = rtrim(System::configure(System::CONF_PATH_TEMPLATE), DIRECTORY_SEPARATOR)
			. DIRECTORY_SEPARATOR . preg_replace('/[^\w\-\.\/\\\\]{1}/', '#', $template) . '.tpl';

		if(!is_file($template))
		{
			System::log()->error('Template file does not exist \'' . $template . '\'', 'Eco');
			System::error('A framework error has occurred');
			return;
		}

		extract($this->__param, EXTR_OVERWRITE);

		include $template; // display template file
	}

	/**
	 * Clear param
	 *
	 * @param string $key
	 * @return void
	 */
	public function clear($key)
	{
		unset($this->__param);
	}

	/**
	 * Param value getter
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function get($key)
	{
		if($this->has($key))
		{
			return $this->__param[$key];
		}

		return null;
	}

	/**
	 * Params getter
	 *
	 * @return array
	 */
	public function getAll()
	{
		return $this->__param;
	}

	/**
	 * Param key exists flag getter
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function has($key)
	{
		return isset($this->__param[$key]) || array_key_exists($key, $this->__param);
	}

	/**
	 * Param setter
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public function set($key, $value)
	{
		$this->__param[$key] = $value;
	}

	/**
	 * Param setter by array
	 *
	 * @param array $params
	 * @return void
	 */
	public function setArray(array $params)
	{
		foreach($params as $k => $v)
		{
			$this->set($k, $v);
		}
	}
}
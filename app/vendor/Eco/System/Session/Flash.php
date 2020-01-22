<?php
/**
 * Eco is a PHP Framework for PHP 5.5+
 *
 * @package Eco
 * @copyright 2015-2020 Shay Anderson <https://www.shayanderson.com>
 * @license MIT License <https://github.com/shayanderson/eco/blob/master/LICENSE>
 * @link <https://github.com/shayanderson/eco>
 */
namespace Eco\System\Session;

use Eco\System\Session;

/**
 * Session flash helper
 *
 * @author Shay Anderson
 */
class Flash extends \Eco\Factory
{
	/**
	 * Session object
	 *
	 * @var \Eco\System\Session
	 */
	private $__session;

	/**
	 * Templates
	 *
	 * @var array
	 */
	private $__templates = [];

	/**
	 * Session key
	 *
	 * @var string
	 */
	public $key = '__ECO__.flash';

	/**
	 * Init session
	 */
	protected function __construct()
	{
		$this->__session = Session::getInstance();
	}

	/**
	 * Flash string getter
	 *
	 * @param string $key
	 * @return string
	 */
	public function get($key)
	{
		$str = '';

		if($this->__session->has($this->key, $key) && is_array($_SESSION[$this->key][$key]))
		{
			foreach($this->__session->get($this->key, $key) as $k => $v)
			{
				// apply message template
				if(isset($this->__templates[$key]) && !empty($this->__templates[$key][1]))
				{
					$v = str_replace('{$message}', $v, $this->__templates[$key][1]);
				}

				$str .= $v;
			}

			// apply group template
			if(isset($this->__templates[$key]) && !empty($this->__templates[$key][0]))
			{
				$str = str_replace('{$message}', $str, $this->__templates[$key][0]);
			}

			$this->__session->clear($this->key, $key); // clear message(s)

			if($this->__session->has($this->key) && !count($_SESSION[$this->key]))
			{
				$this->__session->clear($this->key); // cleanup empty flash
			}
		}

		return $str;
	}

	/**
	 * Key/value setter
	 *
	 * @param string $key
	 * @param string $value
	 * @return void
	 */
	public function set($key, $value)
	{
		if(empty($key))
		{
			return;
		}

		if(!$this->__session->has($this->key, $key))
		{
			$this->__session->setChild($this->key, $key, []);
		}

		$_SESSION[$this->key][$key][] = $value;
	}

	/**
	 * Template setter
	 *
	 * @param string $key
	 * @param string $template
	 * @param string $template_multiple
	 * @return void
	 */
	public function template($key, $template, $template_multiple = '{$message}<br />')
	{
		$this->__templates[$key] = [$template, $template_multiple];
	}
}
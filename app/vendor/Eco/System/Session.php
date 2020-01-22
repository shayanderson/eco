<?php
/**
 * Eco is a PHP Framework for PHP 5.5+
 *
 * @package Eco
 * @copyright 2015-2020 Shay Anderson <https://www.shayanderson.com>
 * @license MIT License <https://github.com/shayanderson/eco/blob/master/LICENSE>
 * @link <https://github.com/shayanderson/eco>
 */
namespace Eco\System;

/**
 * Session helper
 *
 * @author Shay Anderson
 */
class Session extends \Eco\Factory
{
	/**
	 * Init session
	 */
	protected function __construct()
	{
		$this->start();
	}

	/**
	 * Destroy session
	 *
	 * @return void
	 */
	public function destroy()
	{
		$_SESSION = [];

		if(ini_get('session.use_cookies')) // delete session cookie
		{
			$c = session_get_cookie_params();
			setcookie(session_name(), '', time() - 42000,
				$c['path'], $c['domain'], $c['secure'], $c['httponly']);
		}

		session_regenerate_id();
		session_destroy();
	}

	/**
	 * Clear session key
	 *
	 * @param string $key
	 * @param string $child_key (optional, for array: session[key][child_key])
	 * @return void
	 */
	public function clear($key, $child_key = null)
	{
		if(func_num_args() === 1)
		{
			if($this->has($key))
			{
				unset($_SESSION[$key]);
			}
		}
		else
		{
			if($this->has($key, $child_key))
			{
				unset($_SESSION[$key][$child_key]);
			}
		}
	}

	/**
	 * Session value getter
	 *
	 * @param string $key
	 * @param string $child_key (optional, for array: session[key][child_key])
	 * @return mixed
	 */
	public function get($key, $child_key = null)
	{
		if(func_num_args() === 1)
		{
			return $this->has($key) ? $_SESSION[$key] : null;
		}

		return $this->has($key, $child_key) ? $_SESSION[$key][$child_key] : null;
	}

	/**
	 * Session has key flag getter
	 *
	 * @param string $key
	 * @param string $child_key (optional, for array: session[key][child_key])
	 * @return boolean
	 */
	public function has($key, $child_key = null)
	{
		if(func_num_args() === 1)
		{
			return isset($_SESSION[$key]) || array_key_exists($key, $_SESSION);
		}
		else if(isset($_SESSION[$key]) && is_array($_SESSION[$key])) // array key
		{
			return isset($_SESSION[$key][$child_key])
				|| array_key_exists($child_key, $_SESSION[$key]);
		}

		return false;
	}

	/**
	 * Session value setter
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public function set($key, $value)
	{
		$_SESSION[$key] = $value;
	}

	/**
	 * Session array value setter
	 *
	 * @param string $key
	 * @param string $child_key (for array: session[key][child_key])
	 * @return void
	 */
	public function setChild($key, $child_key, $value)
	{
		$_SESSION[$key][$child_key] = $value;
	}

	/**
	 * Start session
	 *
	 * @return void
	 */
	public function start()
	{
		@session_cache_limiter(false);
		@session_start();
	}
}
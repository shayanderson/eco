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
		session_cache_limiter(false);
		session_start();
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

		session_destroy();
		session_regenerate_id();
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
}
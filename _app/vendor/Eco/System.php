<?php
/**
 * Eco is a PHP Framework for PHP 5.5+
 *
 * @package Eco
 * @copyright 2015-2016 Shay Anderson <http://www.shayanderson.com>
 * @license MIT License <https://github.com/shayanderson/eco/blob/master/LICENSE>
 * @link <https://github.com/shayanderson/eco>
 */
namespace Eco;

use Eco\Factory\Breadcrumb;
use Eco\Factory\Filter;
use Eco\Factory\Format;
use Eco\Factory\Keep;
use Eco\Factory\Log;
use Eco\Factory\Request;
use Eco\Factory\Router;
use Eco\Factory\Session;
use Eco\Factory\Session\Flash;
use Eco\Factory\Validate;
use Eco\Factory\View;

/**
 * Eco core class
 *
 * @author Shay Anderson
 */
class System
{
	/**
	 * Error codes
	 */
	const ERROR_FORBIDDEN = 403;
	const ERROR_NOT_FOUND = 404;
	const ERROR_SERVER = 500;

	/**
	 * Hook types
	 */
	const HOOK_AFTER = 'after';
	const HOOK_BEFORE = 'before';
	const HOOK_MIDDLE = 'middle';

	/**
	 * Application configuration settings
	 *
	 * @var \stdClass
	 */
	private static $__conf;

	/**
	 * Last error message
	 *
	 * @var string
	 */
	private static $__error_last;

	/**
	 * Hooks
	 *
	 * @var array
	 */
	private static $__hook = [];

	/**
	 * Array to object (multidimensional array support)
	 *
	 * @param array $arr
	 * @return \stdClass
	 */
	final public static function arrayToObject($array)
	{
		if(is_array($array))
		{
			return (object)array_map(__METHOD__, $array);
		}

		return $array;
	}

	/**
	 * Class autoloader
	 *
	 * @param array $paths
	 * @return void
	 */
	final public static function autoload(array $paths)
	{
		function __autoload($class)
		{
			static $inc_paths = null;

			if(is_array($class)) // setup paths
			{
				$inc_paths = array_map(function($v) { return rtrim($v, DIRECTORY_SEPARATOR)
					. DIRECTORY_SEPARATOR; }, $class);
				return;
			}

			if($inc_paths !== null)
			{
				foreach($inc_paths as $path)
				{
					$file = $path . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';

					if(is_file($file))
					{
						require_once $file;
					}
				}
			}
		}

		spl_autoload_register('\Eco\__autoload'); // register autoloader

		__autoload($paths); // init
	}

	/**
	 * View breadcrumb helper
	 *
	 * @return \Eco\Factory\Breadcrumb
	 */
	final public static function breadcrumb()
	{
		return Breadcrumb::getInstance();
	}

	/**
	 * Global store delete key
	 *
	 * @param string $key
	 * @return void
	 */
	final public static function clear($key)
	{
		Keep::getInstance()->clear($key);
	}

	/**
	* Configuration settings file (must return array) to object
	*
	* @param string $file_path
	* @param boolean $store
	* @return mixed
	*/
	final public static function conf($file_path = null, $store = true)
	{
		if(!func_num_args()) // getter
		{
			return self::$__conf;
		}

		if($store) // internal store
		{
			if(self::$__conf !== null) // merge
			{
				self::$__conf = self::arrayToObject(array_merge((array)require $file_path,
					(array)self::$__conf));
			}
			else
			{
				self::$__conf = self::arrayToObject(require $file_path);
			}
			return;
		}

		return self::arrayToObject(require $file_path);
	}

	/**
	 * Error handler (ex: 403 Forbidden, 404 Not Found, 500 Internal Server Error)
	 *
	 * @staticvar boolean $is_error
	 * @param string $message
	 * @param int $code (ex: 403)
	 * @param string $log_category
	 * @param boolean $http_response_code (set HTTP response code)
	 * @return void
	 */
	final public static function error($message, $code = null, $log_category = null,
		$http_response_code = true)
	{
		static $is_error = false;

		if($is_error)
		{
			self::stop(); // error has been handled
		}
		else
		{
			$is_error = true; // flag error
		}

		if($code === null)
		{
			$code = self::ERROR_SERVER;
		}

		$code = (int)$code;

		if($http_response_code)
		{
			http_response_code($code);
		}

		if(strlen($message))
		{
			self::$__error_last = $message;
		}

		$error_log_level = (int)self::conf()->__eco__->log->error_level;
		if($error_log_level < 3)
		{
			if($error_log_level === 2 || ( $error_log_level === 1 && $code === self::ERROR_SERVER ))
			{
				// log error
				self::log()->error('Error (' . $code . ')' . ( $message !== null
					? ': ' . $message : '' ), $log_category);
			}
		}

		$error_log_write_level = (int)self::conf()->__eco__->log->error_write_level;
		if($error_log_write_level < 3)
		{
			if($error_log_write_level === 2
				|| ( $error_log_write_level === 1 && $code === self::ERROR_SERVER ))
			{
				// write error to log
				error_log('Eco Error (' . $code . '): '
					. ( $message !== null ? $message : 'Unknown error' ));
			}
		}

		Router::getInstance()->action($code);

		self::stop();
	}

	/**
	 * Last error message getter
	 *
	 * @return string
	 */
	final public static function errorGetLast()
	{
		return self::$__error_last;
	}

	/**
	 * Data filter helper
	 *
	 * @return \Eco\Factory\Filter
	 */
	final public static function filter()
	{
		return Filter::getInstance();
	}

	/**
	 * Session flash helper
	 *
	 * @return \Eco\Factory\Session\Flash
	 */
	final public static function flash()
	{
		return Flash::getInstance();
	}

	/**
	 * Data format helper
	 *
	 * @return \Eco\Factory\Format
	 */
	final public static function format()
	{
		return Format::getInstance();
	}

	/**
	 * Global store key value getter
	 *
	 * @param string $key
	 * @return mixed
	 */
	final public static function get($key)
	{
		if($key === null)
		{
			return Keep::getInstance()->getAll();
		}

		return Keep::getInstance()->get($key);
	}

	/**
	 * Global store key exists flag getter
	 *
	 * @param string $key
	 * @return boolean
	 */
	final public static function has($key)
	{
		return Keep::getInstance()->has($key);
	}

	/**
	 * Hook callback
	 *
	 * @param string $name
	 * @param mixed $callback (callable or string for file path for file load)
	 * @return void
	 */
	final public static function hook($name, $callback = null)
	{
		if(func_num_args() === 1) // call
		{
			if(isset(self::$__hook[$name]))
			{
				if(is_callable(self::$__hook[$name])) // callable
				{
					self::log()->debug('Calling hook \'' . $name . '\'', 'Eco');
					call_user_func(self::$__hook[$name]);
				}
				else if(strlen(self::$__hook[$name]) > 0) // file
				{
					require self::$__hook[$name];
					self::log()->debug('Loaded hook \'' . $name . '\' file \''
						. self::$__hook[$name] . '\'', 'Eco');
				}
			}

			return;
		}

		self::$__hook[$name] = $callback;
		self::log()->debug('Hook registered \'' . $name . '\'', 'Eco');
	}

	/**
	 * Log helper
	 *
	 * @return \Eco\Factory\Log
	 */
	final public static function log()
	{
		return Log::getInstance();
	}

	/**
	 * Map param callback
	 *
	 * @param string $id
	 * @param \callable $callback
	 * @return void
	 */
	final public static function param($id, callable $callback)
	{
		Router::getInstance()->mapParamCallback($id, $callback);
	}

	/**
	* Redirect helper
	*
	* @param string $location
	* @param boolean $use_301
	* @return void
	*/
	public static function redirect($location, $use_301 = false)
	{
		if(!headers_sent())
		{
			header('Location: ' . $location, true, $use_301 ? 301 : null);
			self::stop();
		}
	}

	/**
	 * Request helper
	 *
	 * @return \Eco\Factory\Request
	 */
	final public static function request()
	{
		return Request::getInstance();
	}

	/**
	 * Map route
	 *
	 * @param mixed $route (array or string)
	 * @param mixed $action (callable or string)
	 * @return void
	 */
	final public static function route($route, $action = null)
	{
		Router::getInstance()->addRoute($route, $action);
	}

	/**
	 * Router getter
	 *
	 * @return \Eco\Factory\Router
	 */
	final public static function router()
	{
		return Router::getInstance();
	}

	/**
	 * Run application
	 *
	 * @return void
	 */
	final public static function run()
	{
		if(self::conf()->__eco__->request->sanitize_params)
		{
			// sanitize request params
			$_GET = filter_var_array($_GET, FILTER_SANITIZE_STRING);
			$_POST = filter_var_array($_POST, FILTER_SANITIZE_STRING);
		}

		self::hook(self::HOOK_BEFORE);

		// format path
		self::conf()->__eco__->path->controller = rtrim(self::conf()->__eco__->path->controller,
			DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

		Router::getInstance()->dispatch();
	}

	/**
	 * Session helper
	 *
	 * @return \Eco\Factory\Session
	 */
	final public static function session()
	{
		return Session::getInstance();
	}

	/**
	 * Global key/value store setter
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	final public static function set($key, $value)
	{
		Keep::getInstance()->set($key, $value);
	}

	/**
	 * Stop application
	 *
	 * @return void
	 */
	final public static function stop()
	{
		self::hook(self::HOOK_AFTER);
		exit;
	}

	/**
	 * Validate helper
	 *
	 * @return \Eco\Factory\Validate
	 */
	final public static function validate()
	{
		return Validate::getInstance();
	}

	/**
	 * Load view template file
	 *
	 * @param string $template
	 * @param array $view_params (optional, array for params: ['var1' => x, ...])
	 * @return \Eco\Factory\View
	 */
	final public static function view($template = null, array $view_params = null)
	{
		if($template !== null)
		{
			if($view_params !== null)
			{
				View::getInstance()->setArray($view_params);
			}

			View::getInstance()->display($template, $view_params);
		}

		return View::getInstance();
	}
}
<?php
/**
 * Eco is a PHP Framework for PHP 5.5+
 *
 * @package Eco
 * @copyright 2015-2018 Shay Anderson <http://www.shayanderson.com>
 * @license MIT License <https://github.com/shayanderson/eco/blob/master/LICENSE>
 * @link <https://github.com/shayanderson/eco>
 */
namespace Eco;

use Eco\System\Breadcrumb;
use Eco\System\Database;
use Eco\System\Filter;
use Eco\System\Format;
use Eco\System\Keep;
use Eco\System\Log;
use Eco\System\Request;
use Eco\System\Router;
use Eco\System\Session;
use Eco\System\Session\Flash;
use Eco\System\Validate;
use Eco\System\View;

/**
 * Eco core
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
	 * Last error category
	 *
	 * @var string
	 */
	private static $__error_last_category;

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
	 * Registry loader
	 *
	 * @staticvar array $registry
	 * @param string $class
	 * @return \Eco\System\Registry
	 */
	private static function __registry($type, $class)
	{
		static $registry;

		if(!isset($registry[$type]))
		{
			require_once PATH_COM . $type . '.php';

			if(!class_exists($class))
			{
				throw new \Exception(__METHOD__ . ': ' . $type . ' load failed, \'' . $class . '\''
					. ' class not found');
			}

			// parse class annotations
			preg_match_all('#@property\s([^\s]+)\s\$([\w]+)#', // match '@property <class> $<name>'
				(new \ReflectionClass($class))->getDocComment(), $m);

			if(isset($m[1]) && $m[1])
			{
				foreach($m[1] as $k => $v)
				{
					if(isset($m[2][$k])) // name
					{
						$registry[$type][trim($m[2][$k])] = trim($v);
					}
				}
			}

			unset($m);

			if($registry[$type]) // initialize
			{
				$class::getInstance()->__init($registry[$type]);
			}
		}

		return $class::getInstance();
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
	 * View breadcrumb object getter
	 *
	 * @return \Eco\System\Breadcrumb
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
	* @param mixed $path_or_array (string for file path or array for settings)
	* @param boolean $store
	* @return mixed
	*/
	final public static function conf($path_or_array = null, $store = true)
	{
		if(!func_num_args()) // getter
		{
			return self::$__conf;
		}

		if($store) // internal store
		{
			if(self::$__conf !== null) // merge
			{
				self::$__conf = self::arrayToObject(array_merge(is_array($path_or_array)
					? $path_or_array : (array)require $path_or_array, (array)self::$__conf));
			}
			else
			{
				self::$__conf = self::arrayToObject(is_array($path_or_array) ? $path_or_array
					: require $path_or_array);
			}
			return;
		}

		return self::arrayToObject(is_array($path_or_array) ? $path_or_array
			: require $path_or_array);
	}

	/**
	 * Database object getter
	 *
	 * @param mixed $connection_id
	 * @return \Eco\System\Database
	 */
	final public static function db($connection_id = null)
	{
		if($connection_id !== null) // connection ID setter
		{
			Database::getInstance()->connection($connection_id);
		}

		return Database::getInstance();
	}

	/**
	 * Error handler (ex: 403 Forbidden, 404 Not Found, 500 Internal Server Error)
	 *
	 * @staticvar boolean $is_error
	 * @param string $message
	 * @param int $code (ex: 403)
	 * @param string $category
	 * @param boolean $http_response_code (set HTTP response code)
	 * @return void
	 */
	final public static function error($message, $code = null, $category = null,
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
		if($category)
		{
			self::$__error_last_category = $category;
		}

		$error_log_level = (int)self::conf()->_eco->log->error_level;
		if($error_log_level < 3)
		{
			if($error_log_level === 2 || ( $error_log_level === 1 && $code === self::ERROR_SERVER ))
			{
				// log error
				self::log()->error('Error (' . $code . ')' . ( $message !== null
					? ': ' . $message : '' ), $category);
			}
		}

		$error_log_write_level = (int)self::conf()->_eco->log->error_write_level;
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
	 * Last error category getter
	 *
	 * @return string
	 */
	final public static function errorGetLastCategory()
	{
		return self::$__error_last_category;
	}

	/**
	 * Data filter object getter
	 *
	 * @return \Eco\System\Filter
	 */
	final public static function filter()
	{
		return Filter::getInstance();
	}

	/**
	 * Session flash object getter
	 *
	 * @return \Eco\System\Session\Flash
	 */
	final public static function flash()
	{
		return Flash::getInstance();
	}

	/**
	 * Data format object getter
	 *
	 * @return \Eco\System\Format
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
	 * Log object getter
	 *
	 * @return \Eco\System\Log
	 */
	final public static function log()
	{
		return Log::getInstance();
	}

	/**
	 * Model loader + registry
	 *
	 * @return \EcoModelRegistry
	 */
	final public static function model()
	{
		return self::__registry(\Eco\System\Registry\Model::ID, '\EcoModelRegistry');
	}

	/**
	 * Map param callback
	 *
	 * @param mixed $id (array|string)
	 * @param \callable $callback
	 * @return void
	 */
	final public static function param($id, callable $callback)
	{
		Router::getInstance()->mapParamCallback($id, $callback);
	}

	/**
	* Redirect method
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
	 * Request object getter
	 *
	 * @return \Eco\System\Request
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
	 * @return \Eco\System\Router
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
		if(self::conf()->_eco->request->sanitize_params)
		{
			// sanitize request params
			$_GET = filter_var_array($_GET, FILTER_SANITIZE_STRING);
			$_POST = filter_var_array($_POST, FILTER_SANITIZE_STRING);
		}

		self::hook(self::HOOK_BEFORE);

		// format path
		self::conf()->_eco->path->controller = rtrim(self::conf()->_eco->path->controller,
			DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

		Router::getInstance()->dispatch();
	}

	/**
	 * Service loader + registry
	 *
	 * @return \EcoServiceRegistry
	 */
	final public static function service()
	{
		return self::__registry(\Eco\System\Registry\Service::ID, '\EcoServiceRegistry');
	}

	/**
	 * Session object getter
	 *
	 * @return \Eco\System\Session
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
	 * Validate object getter
	 *
	 * @return \Eco\System\Validate
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
	 * @return \Eco\System\View
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
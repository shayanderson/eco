<?php
/**
 * Eco is a PHP Framework for PHP 5.5+
 *
 * @package Eco
 * @copyright 2015-2017 Shay Anderson <http://www.shayanderson.com>
 * @license MIT License <https://github.com/shayanderson/eco/blob/master/LICENSE>
 * @link <https://github.com/shayanderson/eco>
 */
namespace Eco\System;

use Eco\System;

/**
 * Router
 *
 * @author Shay Anderson
 */
class Router extends \Eco\Factory
{
	/**
	 * 404 callback
	 *
	 * @var callable
	 */
	private $__404_callback;

	/**
	 * Params
	 *
	 * @var array
	 */
	private $__param = [];

	/**
	 * Param callbacks
	 *
	 * @var array
	 */
	private $__param_callback = [];

	/**
	 * Request parts
	 *
	 * @var array
	 */
	private $__request;

	/**
	 * Routes
	 *
	 * @var array
	 */
	private $__route = [];

	/**
	 * Route callbacks
	 *
	 * @var array
	 */
	private $__route_callback = [];

	/**
	 * CLI only routes
	 *
	 * @var array
	 */
	private $__route_cli = [];

	/**
	 * Request
	 *
	 * @var string
	 */
	public $request;

	/**
	 * Route
	 *
	 * @var string
	 */
	public $route;

	/**
	 * Init
	 */
	protected function __construct()
	{
		// set request
		if(isset($_SERVER['REQUEST_URI']))
		{
			$request = $_SERVER['REQUEST_URI'];
		}
		else if($this->isCli()) // CLI
		{
			if(isset($_SERVER['argv'][1]))
			{
				$request = $_SERVER['argv'][1];
			}
			else
			{
				System::log()->error('Failed to detect CLI route', 'Eco');
				$request = '/';
			}
		}
		else
		{
			System::log()->warning('Failed to detect request', 'Eco');
			$request = '/';
		}

		if(($pos = strpos($request, '?')) !== false)
		{
			$request = substr($request, 0, $pos); // strip query string
		}

		if($request[0] === '/')
		{
			$request = substr($request, 1); // strip first sep
		}

		$this->__request = array_map('urldecode', explode('/', $request));
		$this->request = '/' . implode('/', $this->__request);
	}

	/**
	 * Format class name for dynamic loading
	 *
	 * @param string $class
	 * @return string
	 */
	private function __classFormat($class)
	{
		return ($pos = strrpos($class, DIRECTORY_SEPARATOR)) !== false
			? substr($class, $pos + 1) : $class;
	}

	/**
	 * Class loader
	 *
	 * @param string $class
	 * @param string $type
	 * @return boolean (false on load fail)
	 */
	private function __classLoad($class, $type)
	{
		$class_path = System::conf()->__eco__->path->controller
			. str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';

		if(class_exists($class, false)) // class is ready
		{
			return true;
		}

		if(is_file($class_path))
		{
			require_once $class_path;
			$class = $this->__classFormat($class);

			if(class_exists($class))
			{
				System::log()->debug($type . ' class \'' . $class . '\' loaded from \''
					. $class_path . '\'', 'Eco');
				return true;
			}

			System::error('Class \'' . $class . '\' does not exist in class file \''
				. $class_path . '\'', null, 'Eco');
		}
		else
		{
			System::error('Class path does not exist \'' . $class_path . '\'', null, 'Eco');
		}

		return false;
	}

	/**
	 * Class route loader
	 *
	 * @param string $class_method
	 * @return void
	 */
	private function __classRouteLoader($class_method)
	{
		$loader = explode('::', $class_method);

		if(count($loader) === 2) // static method
		{
			if($this->__classLoad($loader[0], 'Route loader'))
			{
				$loader[0] = $this->__classFormat($loader[0]);

				if(method_exists($loader[0], $loader[1])
					&& (new \ReflectionMethod("{$loader[0]}::{$loader[1]}"))->isStatic())
				{
					$load = $loader[0]::{$loader[1]}(); // call method loader

					if(is_array($load)) // array loader
					{
						foreach($load as &$v)
						{
							if(strpos($v, '->') === false) // add class name
							{
								$v = $loader[0] . '->' . $v;
							}
						}

						$this->addRoute($load);
					}
				}
				else
				{
					System::error('Route loader class \'' . $loader[0] . '\' method \''
						. $loader[1] . '\' does not exist or is not a static method', null, 'Eco');
				}
			}
		}
		else
		{
			System::error('Invalid route loader method call for \'' . $class_method . '\','
				. ' must be a static method call', null, 'Eco');
		}
	}

	/**
	 * Apply param callback
	 *
	 * @param string $id
	 * @param string $value
	 * @return string
	 */
	private function __paramCallback($id, $value)
	{
		if(isset($this->__param_callback[$id]))
		{
			return $this->__param_callback[$id]($value);
		}

		return $value;
	}

	/**
	 * Call route callbacks
	 *
	 * @param string $route_id
	 * @return void
	 */
	private function __routeCallback($route_id)
	{
		if(isset($this->__route_callback[$route_id])) // route callbacks
		{
			foreach($this->__route_callback[$route_id] as $f)
			{
				$f();
			}
		}
	}

	/**
	 * Call route action
	 *
	 * @param mixed $route_id
	 * @return boolean (false on failed action)
	 */
	public function action($route_id)
	{
		if(isset($this->__route[$route_id]))
		{
			$action = $this->__route[$route_id];

			if(is_string($action)) // string class/func
			{
				if(strpos($action, '->') !== false) // 'Class->method'
				{
					$action = explode('->', $action);

					if($this->__classLoad($action[0], 'Controller'))
					{
						$action[0] = $this->__classFormat($action[0]);

						if(method_exists($action[0], $action[1])) // verify method
						{
							System::hook(System::HOOK_MIDDLE);
							$this->__routeCallback($route_id);
							System::log()->debug('Calling route action \'' . $action[0] . '->'
								. $action[1] . '\'', 'Eco');
							$action[0] = new $action[0];
							call_user_func_array([$action[0], $action[1]], $this->__param);
							return true;
						}
						else
						{
							System::error('Class \'' . $action[0] . '\' method \''
								. $action[1] .'\' does not exist', null, 'Eco');
						}
					}
				}
				else
				{
					System::error('Invalid route Class->method call for \'' . $action . '\'',
						null, 'Eco');
				}
			}
			else if(is_callable($action)) // func
			{
				System::hook(System::HOOK_MIDDLE);
				$this->__routeCallback($route_id);
				System::log()->debug('Calling route action', 'Eco');
				call_user_func_array($action, $this->__param);
				return true;
			}
		}

		return false; // unhandled
	}

	/**
	 * Map route
	 *
	 * @param mixed $route (array or string)
	 * @param mixed $action (callable or string)
	 * @return void
	 */
	public function addRoute($route, $action = null)
	{
		if(is_array($route))
		{
			foreach($route as $k => $v)
			{
				$this->addRoute($k, $v);
			}

			return;
		}

		$key = ltrim($route, '/');

		if($key && $key[0] === '$') // cli only
		{
			$key = substr($key, 1);
			$this->__route_cli[$key] = true;
		}

		if(isset($this->__route[$key]))
		{
			System::error('Cannot redeclare route \'' . $route . '\'', null, 'Eco');
			return;
		}

		if(is_array($action)) // route/param callbacks
		{
			$parts = $action;
			$action = null;
			foreach($parts as $v)
			{
				if($action === null) // set action string
				{
					$action = $v;
					continue;
				}

				if(is_array($v)) // param callback
				{
					foreach($v as $k => $cb)
					{
						$this->mapParamCallback($k, $cb);
					}
				}
				else // route callback
				{
					if(!isset($this->__route_callback[$key]))
					{
						$this->__route_callback[$key] = [];
					}

					$this->__route_callback[$key][] = $v;
				}
			}
		}

		$this->__route[$key] = $action;
	}

	/**
	 * Clear route param
	 *
	 * @param string $id
	 * @return void
	 */
	public function clearParam($id)
	{
		unset($this->__param[$id]);
	}

	/**
	 * Dispatcher
	 *
	 * @return void
	 */
	public function dispatch()
	{
		$request = $this->__request;
		$route_id = null;

		// set action
		if(isset($request[0]) && empty($request[0])) // index action
		{
			$route_id = '';
			System::log()->debug('Route detected for index', 'Eco');
		}
		else // non-index action
		{
			$is_route_loader = false;

			// routing
			while(list($route, $v) = each($this->__route))
			{
				$mapped = explode('/', $route);
				$count_mapped = count($mapped);
				$i = 0;
				$param_wc_id = null;

				foreach($mapped as $k => $part)
				{
					$i++;

					if(!empty($part) && $part[0] === ':') // param(s)
					{
						$regex = null;
						if(($p = strpos($part, '@')) !== false) // param regex: ':param@regex'
						{
							$regex = substr($part, $p + 1);
							$part = substr($part, 0, $p);
						}

						if(($p = strrpos($part, ':')) > 0) // param callback ':param:callback'
						{
							$cb = substr($part, $p + 1, strlen($part));
							$part = substr($part, 0, $p);
							$this->mapParamCallback(substr($part, 1), $cb);
						}

						if(isset($request[$k]) && strlen($request[$k]) > 0) // valid param
						{
							if($regex && substr($part, -1) !== '+' // do not test wildcard params
								&& !preg_match('#^' . $regex . '$#', $request[$k]))
							{
								continue 2; // regex failed
							}

							if(substr($part, -1) === '+') // wildcard params
							{
								$param_wc_id = substr(rtrim($part, '+'), 1);

								foreach(array_slice($request, $k) as $p) // add wildcard params
								{
									if($regex && !preg_match('#^' . $regex . '$#', $p))
									{
										continue 3; // regex failed on wildcard param
									}

									$this->__param[$param_wc_id][] = $this->__paramCallback(
										$param_wc_id, urldecode($p));
								}
							}
							else // param or optional param
							{
								$part_k = substr(rtrim($part, '?'), 1);
								$this->__param[$part_k] = $this->__paramCallback($part_k,
									urldecode($request[$k]));
							}
						}
						else if(substr($part, -1) !== '?') // check for optional param
						{
							continue 2; // required param missing
						}
					}
					else if(!isset($request[$k]) || $part !== $request[$k]) // parts do not match
					{
						// check for class route loader
						if(!$is_route_loader && isset($request[$k]) && substr($part, -1) === '*'
							&& substr($part, 0, -1) == $request[$k])
						{
							$this->__classRouteLoader($v); // invoke class route loader
							$is_route_loader = true;
							reset($this->__route);
						}
						continue 2;
					}

					if($i === $count_mapped || $param_wc_id !== null) // end of mapped route
					{
						// look forward check if request continues
						if($param_wc_id === null && count($request) > $count_mapped)
						{
							continue 2; // count mismatch
						}

						// set route
						$route_id = $route;
						System::log()->debug('Mapped route detected \'' . $route_id . '\'', 'Eco');
						break 2;
					}
				}
			}
		}

		$this->route = '/' . $route_id;

		// no route || CLI only route + not CLI run
		if($route_id === null || ( isset($this->__route_cli[$route_id]) && !$this->isCli() ))
		{
			$route_id = null;

			if($this->__404_callback !== null)
			{
				System::hook(System::HOOK_MIDDLE);

				$handled = call_user_func($this->__404_callback, $this->request);

				System::log()->debug('404 callback called');

				if($handled)
				{
					System::stop();
				}
			}

			System::log()->debug('Failed to find route for request \''
				. $this->request . '\'', 'Eco');
		}

		// call action
		if(($route_id === null ? System::ERROR_NOT_FOUND : $this->action($route_id)) !== true)
		{
			System::error('Not found: \'' . $this->request . '\'', System::ERROR_NOT_FOUND, 'Eco');
		}

		System::stop();
	}

	/**
	 * Param getter
	 *
	 * @param string $id
	 * @return string (or null on does not exist)
	 */
	public function getParam($id)
	{
		if($this->hasParam($id))
		{
			return $this->__param[$id];
		}

		return null;
	}

	/**
	 * Params getter
	 *
	 * @return array
	 */
	public function getParams()
	{
		return $this->__param;
	}

	/**
	 * Param exists flag getter
	 *
	 * @param string $id
	 * @return boolean
	 */
	public function hasParam($id)
	{
		return isset($this->__param[$id]) || array_key_exists($id, $this->__param);
	}

	/**
	 * Detect CLI run
	 *
	 * @return boolean
	 */
	public function isCli()
	{
		return php_sapi_name() === 'cli';
	}

	/**
	 * Map param callback
	 *
	 * @param mixed $id (array|string)
	 * @param \callable $callback
	 * @return void
	 */
	public function mapParamCallback($id, callable $callback)
	{
		if(is_array($id))
		{
			foreach($id as $v)
			{
				$this->mapParamCallback($v, $callback);
			}

			return;
		}

		$id = str_replace(['?', '+'], '', $id); // strip special chars
		$this->__param_callback[$id] = $callback;
	}

	/**
	 * 404 callback setter
	 *
	 * @param \callable $callback
	 * @return void
	 */
	public function set404Callback(callable $callback)
	{
		$this->__404_callback = $callback;
	}
}
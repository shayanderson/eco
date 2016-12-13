<?php
/**
 * Eco is a PHP Framework for PHP 5.5+
 *
 * @package Eco
 * @copyright 2015-2016 Shay Anderson <http://www.shayanderson.com>
 * @license MIT License <https://github.com/shayanderson/eco/blob/master/LICENSE>
 * @link <https://github.com/shayanderson/eco>
 */

/**
 * Object factory helper functions
 */

/**
 * Object factory helper
 *
 * @param mixed $args (array|object)
 * @param mixed $class_name (string for class name, array for class name + method)
 * @param bool $use_as_single_arg
 * @return mixed (array|object)
 */
function &factory($args, $class_name, $use_as_single_arg = true)
{
	$obj = null;

	// single
	if(($is_obj = is_object($args)) || ( !is_array(current($args)) && !is_object(current($args)) ))
	{
		if($is_obj && !$use_as_single_arg)
		{
			$args = (array)$args;
		}

		if(is_array($class_name))
		{
			list($class_name, $method) = $class_name;
		}

		if(!isset($method))
		{
			$obj = $use_as_single_arg
				? new $class_name($args)
				: (new \ReflectionClass($class_name))->newInstanceArgs($args);
		}
		else // method
		{
			$obj = new $class_name;

			if($use_as_single_arg)
			{
				$obj->{$method}($args);
			}
			else
			{
				call_user_func_array([$obj, $method], $args);
			}
		}
	}
	else // multiple
	{
		$obj = [];

		foreach($args as $v)
		{
			$obj[] = call_user_func(__FUNCTION__, $v, $class_name, $use_as_single_arg);
		}
	}

	return $obj;
}

/**
 * Object factory properties helper
 *
 * @param object $object
 * @param mixed $props (array|object)
 * @param boolean $use_prop_must_exist
 * @return void
 */
function factory_props(&$object, $props, $use_prop_must_exist = true)
{
	if(is_object($props))
	{
		$props = (array)$props;
	}

	foreach($props as $k => $v)
	{
		if($use_prop_must_exist)
		{
			$r = new \ReflectionObject($object);

			if(!$r->hasProperty($k) || !$r->getProperty($k)->isPublic())
			{
				continue;
			}
		}

		$object->{$k} = $v;
	}
}
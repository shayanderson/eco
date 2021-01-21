<?php
/**
 * Eco is a PHP Framework for PHP 5.5+
 *
 * @package Eco
 * @copyright 2015-2021 Shay Anderson <https://www.shayanderson.com>
 * @license MIT License <https://github.com/shayanderson/eco/blob/master/LICENSE>
 * @link <https://github.com/shayanderson/eco>
 */

/**
 * View helper functions
 */

/**
 * Decorate values
 *
 * @param string $decorator
 * @param mixed $value
 * @param callable $filter
 * @param boolean $is_indexed_array
 * @return string
 */
function decorate($decorator, $value, callable $filter = null, $is_indexed_array = false)
{
	$pattern = '/{\$([\w]+)}/i';
	$count = is_scalar($value) ? 0 : count((array)$value);

	if(is_string($decorator) && $count)
	{
		if($is_indexed_array)
		{
			$s = '';
			foreach($value as $k => $v)
			{
				if($filter)
				{
					$v = $filter($v, $k);
				}

				$s .= str_replace('{$key}', $k, str_replace('{$value}', $v, $decorator));
			}
			return $s;
		}

		// one-dimensional array
		if($count === count((array)$value, COUNT_RECURSIVE) && !is_object(current($value)))
		{
			if(is_object($value)) // object, force array
			{
				$value = (array)$value;
			}

			if($filter) // apply filter
			{
				$value = (array)$filter((object)$value);

				if(!$value) // filter did not return value
				{
					trigger_error('Decorate filter returned empty value', E_USER_WARNING);
					return;
				}
			}

			preg_replace_callback($pattern, function($m) use(&$value, &$decorator, &$filter)
			{
				if((isset($value[$m[1]]) || array_key_exists($m[1], $value))
					&& ($value[$m[1]] === null || is_scalar($value[$m[1]])))
				{
					// decorate values
					$decorator = str_replace($m[0], $value[$m[1]], $decorator);
				}
				else // auto rm unset vars
				{
					$decorate = str_replace($m[0], '', $decorator);
				}
			}, $decorator);
		}
		else // multidimensional array
		{
			$str = '';

			foreach($value as $v)
			{
				foreach($v as $k => $vv)
				{
					if(is_array($vv))
					{
						unset($v[$k]); // depth not allowed
					}
				}

				$str .= call_user_func_array(__FUNCTION__, [$decorator, $v, $filter]);
			}

			$decorator = &$str;
		}

		return $decorator;
	}

	return ''; // no value
}

/**
 * Prepare safe HTML output string
 *
 * @param string $value
 * @return string
 */
function html($value)
{
	return filter_var(htmlspecialchars($value), FILTER_SANITIZE_STRING);
}
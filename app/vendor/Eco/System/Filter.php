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

/**
 * Data filter helper
 *
 * @author Shay Anderson
 */
class Filter extends \Eco\Factory
{
	/**
	 * Strip non-alphanumeric characters
	 *
	 * @param mixed $value
	 * @param boolean $allow_whitespaces
	 * @return mixed
	 */
	public function alnum($value, $allow_whitespaces = false)
	{
		return $allow_whitespaces ? preg_replace('/[^a-zA-Z0-9\s]+/', '', $value)
			: preg_replace('/[^a-zA-Z0-9]+/', '', $value);
	}

	/**
	 * Strip non-alpha characters
	 *
	 * @param mixed $value
	 * @param boolean $allow_whitespaces
	 * @return mixed
	 */
	public function alpha($value, $allow_whitespaces = false)
	{
		return $allow_whitespaces ? preg_replace('/[^a-zA-Z\s]+/', '', $value)
			: preg_replace('/[^a-zA-Z]+/', '', $value);
	}

	/**
	 * Strip non-date characters
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	public function date($value)
	{
		return preg_replace('/[^0-9\-\/]/', '', $value);
	}

	/**
	 * Strip non-date/time characters
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	public function dateTime($value)
	{
		return preg_replace('/[^0-9\-\/\:\s]/', '', $value);
	}

	/**
	 * Strip non-decimal characters
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	public function decimal($value)
	{
		$value = filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

		if(substr_count($value, '.') > 1) // multiple '.', only allow one
		{
			$value = substr($value, 0, strpos($value, '.', 2));
		}

		return $value;
	}

	/**
	 * Strip non-email characters
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	public function email($value)
	{
		return filter_var($value, FILTER_SANITIZE_EMAIL);
	}

	/**
	 * Encode HTML special characters
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	public function htmlEncode($value)
	{
		return filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS);
	}

	/**
	 * Strip non-numeric characters
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	public function numeric($value)
	{
		return filter_var($value, FILTER_SANITIZE_NUMBER_INT);
	}

	/**
	 * Strip HTML tags from a string
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	public function sanitize($value)
	{
		return filter_var($value, FILTER_SANITIZE_STRING);
	}

	/**
	 * Strip non-time characters
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	public function time($value)
	{
		return preg_replace('/[^0-9\:]/', '', $value);
	}

	/**
	 * Strip non-word characters (same as character class '\w')
	 *
	 * @param mixed $value
	 * @param boolean $allow_whitespaces
	 * @return mixed
	 */
	public function word($value, $allow_whitespaces = false)
	{
		return $allow_whitespaces ? preg_replace('/[^\w\s]/', '', $value)
			: preg_replace('/[^\w]/', '', $value);
	}
}
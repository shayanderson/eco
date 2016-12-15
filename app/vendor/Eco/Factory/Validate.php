<?php
/**
 * Eco is a PHP Framework for PHP 5.5+
 *
 * @package Eco
 * @copyright 2015-2016 Shay Anderson <http://www.shayanderson.com>
 * @license MIT License <https://github.com/shayanderson/eco/blob/master/LICENSE>
 * @link <https://github.com/shayanderson/eco>
 */
namespace Eco\Factory;

/**
 * Data validate helper
 *
 * @author Shay Anderson
 */
class Validate extends \Eco\Factory
{
	/**
	 * Validate value is alphanumeric characters
	 *
	 * @param mixed $value
	 * @param boolean $allow_whitespaces
	 * @return boolean
	 */
	public function alnum($value, $allow_whitespaces = false)
	{
		return $allow_whitespaces ? preg_match('/^[a-zA-Z0-9\s]+$/', $value) : ctype_alnum($value);
	}

	/**
	 * Validate value is alpha characters
	 *
	 * @param mixed $value
	 * @param boolean $allow_whitespaces
	 * @return boolean
	 */
	public function alpha($value, $allow_whitespaces = false)
	{
		return $allow_whitespaces ? preg_match('/^[a-zA-Z\s]+$/', $value) : ctype_alpha($value);
	}

	/**
	 * Validate value between min and max values
	 *
	 * @param mixed $value
	 * @param int min
	 * @param int max
	 * @return boolean
	 */
	public function between($value, $min, $max)
	{
		return $value >= $min && $value <= $max;
	}

	/**
	 * Validate value contains value
	 *
	 * @param mixed $value
	 * @param mixed $contain_value
	 * @param boolean $case_insensitive
	 * @return boolean
	 */
	public function contains($value, $contain_value, $case_insensitive = false)
	{
		return $case_insensitive ? stripos($value, $contain_value) !== false
			: strpos($value, $contain_value) !== false;
	}

	/**
	 * Validate value does not contain value
	 *
	 * @param mixed $value
	 * @param mixed $contain_not_value
	 * @param boolean $case_insensitive
	 * @return boolean
	 */
	public function containsNot($value, $contain_not_value, $case_insensitive = false)
	{
		return $case_insensitive ? stripos($value, $contain_not_value) === false
			: strpos($value, $contain_not_value) === false;
	}

	/**
	 * Validate value is decimal
	 *
	 * @param mixed $value
	 * @return boolean
	 */
	public function decimal($value)
	{
		if(preg_match('/^[0-9\.]+$/', $value))
		{
			return substr_count($value, '.') <= 1;
		}

		return false;
	}

	/**
	 * Validate value is email
	 *
	 * @param mixed $value
	 * @return boolean
	 */
	public function email($value)
	{
		return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
	}

	/**
	 * Validate value is IPv4 address
	 *
	 * @param mixed $value
	 * @return boolean
	 */
	public function ipv4($value)
	{
		return filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
	}

	/**
	 * Validate value is IPv6 address
	 *
	 * @param mixed $value
	 * @return boolean
	 */
	public function ipv6($value)
	{
		return filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
	}

	/**
	 * Validate value min length, under max length, or between min and max lengths, or exact length
	 *
	 * @param mixed $value
	 * @param int $min
	 * @param int $max
	 * @param int $exact
	 * @return boolean
	 */
	public function length($value, $min = 0, $max = 0, $exact = 0)
	{
		$min = (int)$min;
		$max = (int)$max;
		$exact = (int)$exact;

		if($min && $max)
		{
			return strlen($value) >= $min && strlen($value) <= $max;
		}
		else if($min)
		{
			return strlen($value) >= $min;
		}
		else if($max)
		{
			return strlen($value) <= $max;
		}
		else if($exact)
		{
			return strlen($value) === $exact;
		}

		return false;
	}

	/**
	 * Validate value is match to value
	 *
	 * @param mixed $value
	 * @param mixed $compare_value
	 * @param boolean $case_insensitive
	 * @return boolean
	 */
	public function match($value, $compare_value, $case_insensitive = false)
	{
		return $case_insensitive ? strcasecmp($value, $compare_value) === 0
			: strcmp($value, $compare_value) === 0;
	}

	/**
	 * Validate value is numeric
	 *
	 * @param mixed $value
	 * @return boolean
	 */
	public function numeric($value)
	{
		return preg_match('/^[0-9]+$/', $value);
	}

	/**
	 * Validate Perl-compatible regex pattern
	 *
	 * @param string $pattern
	 * @return boolean
	 */
	public function regexPattern($pattern)
	{
		return @preg_match($pattern, '') !== false;
	}

	/**
	 * Validate value exists (length(trim(value)) > 0)
	 *
	 * @param mixed $value
	 * @return boolean
	 */
	public function required($value)
	{
		return strlen(trim($value)) > 0;
	}

	/**
	 * Validate value is URL
	 *
	 * @param mixed $value
	 * @return boolean
	 */
	public function url($value)
	{
		return filter_var($value, FILTER_VALIDATE_URL) !== false;
	}

	/**
	 * Validate value is word (same as character class '\w')
	 *
	 * @param mixed $value
	 * @param boolean $allow_whitespaces
	 * @return boolean
	 */
	public function word($value, $allow_whitespaces = false)
	{
		return $allow_whitespaces ? preg_match('/^[\w\s]+$/', $value) : preg_match('/^[\w]+$/',
			$value);
	}
}
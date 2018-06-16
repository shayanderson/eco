<?php
/**
 * Eco is a PHP Framework for PHP 5.5+
 *
 * @package Eco
 * @copyright 2015-2018 Shay Anderson <http://www.shayanderson.com>
 * @license MIT License <https://github.com/shayanderson/eco/blob/master/LICENSE>
 * @link <https://github.com/shayanderson/eco>
 */
namespace Eco\System;

/**
 * Data format helper
 *
 * @author Shay Anderson
 */
class Format extends \Eco\Factory
{
	/**
	 * Default currency format (ex: '$%0.2f')
	 *
	 * @var string (sprintf format: <http://www.php.net/manual/en/function.sprintf.php>)
	 */
	public $default_format_currency = '$%0.2f';

	/**
	 * Default date format (ex: 'm/d/Y')
	 *
	 * @var string (date/time format: <http://www.php.net/manual/en/function.date.php>)
	 */
	public $default_format_date = 'm/d/Y';

	/**
	 * Default date/time format (ex: 'm/d/Y H:i:s')
	 *
	 * @var string (date/time format: <http://www.php.net/manual/en/function.date.php>)
	 */
	public $default_format_date_time = 'm/d/Y H:i:s';

	/**
	 * Default time format (ex: 'H:i:s')
	 *
	 * @var string (date/time format: <http://www.php.net/manual/en/function.date.php>)
	 */
	public $default_format_time = 'H:i:s';

	/**
	* URL safe base64 decode value
	*
	* @param string $value
	* @return string
	*/
	public function base64UrlDecode($value)
	{
		return base64_decode(str_pad(strtr($value, '-_', '+/'), strlen($value) % 4, '=',
			STR_PAD_RIGHT));
	}

	/**
	 * URL safe base64 encode value
	 *
	 * @param string $value
	 * @return string
	 */
	public function base64UrlEncode($value)
	{
		return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
	}

	/**
	 * Format byte (ex: 2000 => '1.95 kb')
	 *
	 * @param float $value
	 * @param array $characters (ex: [' b', ' kb', ' mb', ' gb', ' tb', ' pb'])
	 * @return string (or false on invalid value)
	 */
	public function byte($value,
		array $characters = [' b', ' kb', ' mb', ' gb', ' tb', ' pb'])
	{
		$value = (float)$value;

		if($value <= 0)
		{
			return '0' . $characters[0];
		}

		return round($value / pow(1024, ( $k = floor(log($value, 1024)) )), 2) . $characters[$k];
	}

	/**
	 * Format currency
	 *
	 * @param mixed $value
	 * @param mixed $format
	 * @return mixed
	 */
	public function currency($value, $format = null)
	{
		return sprintf($format !== null ? $format : $this->default_format_currency, $value);
	}

	/**
	 * Format date
	 *
	 * @param mixed $value
	 * @param mixed $format
	 * @return string
	 */
	public function date($value, $format = null)
	{
		if(empty($value))
		{
			return '';
		}

		return date($format !== null ? $format : $this->default_format_date, strtotime($value));
	}

	/**
	 * Format date/time
	 *
	 * @param mixed $value
	 * @param mixed $format
	 * @return string
	 */
	public function dateTime($value, $format = null)
	{
		if(empty($value))
		{
			return '';
		}

		return date($format !== null ? $format : $this->default_format_date_time,
			strtotime($value));
	}

	/**
	 * Format name key, ex: 'Test string here.' => 'test-string-here'
	 *
	 * @param string $value
	 * @param string $additional_allowed_chars (ex: '\')
	 * @return string
	 */
	public function nameKey($value, $additional_allowed_chars = null)
	{
		$value = preg_replace('@[^a-z0-9\-' . preg_quote($additional_allowed_chars) . ']@', '-',
			strtolower(trim($value)));
		$value = preg_replace('/\-+/', '-', $value); // '--' to '-'

		return trim($value, '-');
	}

	/**
	 * Format name key, ex: 'Test data here.' => 'test-data-here'
	 *
	 * @param mixed $value
	 * @return string
	 */
	public function time($value, $format = null)
	{
		if(empty($value))
		{
			return '';
		}

		return date($format !== null ? $format : $this->default_format_time, strtotime($value));
	}

	/**
	 * Format time elapsed
	 *
	 * @param float $time_elapsed (ex: microtime(true) - $start)
	 * @param array $characters (ex: [' years', ' weeks', ' days', ' hours', ' minutes', ' seconds'])
	 * @return string (ex: '1h 35m 55s')
	 */
	public function timeElapsed($time_elapsed,
		array $characters = ['y', 'w', 'd', 'h', 'm', 's'])
	{
		$b = [
			$characters[0] => $time_elapsed / 31556926 % 12,
			$characters[1] => $time_elapsed / 604800 % 52,
			$characters[2] => $time_elapsed / 86400 % 7,
			$characters[3] => $time_elapsed / 3600 % 24,
			$characters[4] => $time_elapsed / 60 % 60,
			$characters[5] => $time_elapsed % 60,
		];

		$out = [];
		foreach($b as $k => $v)
		{
			if($v > 0)
			{
				$out[] = $v . $k;
			}
		}

		return implode(' ', $out);
	}
}
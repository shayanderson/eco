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
	 * @param array $characters (ex: [' B', ' KB', ' MB', ' GB', ' TB', ' PB'])
	 * @return string (or false on invalid value)
	 */
	public function byte($value,
		array $characters = [' B', ' KB', ' MB', ' GB', ' TB', ' PB'])
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
	 * Format relative date (past values)
	 *
	 * @param string $value (ex: "05/22/2019 10:55:00")
	 * @param string $value_compare
	 * @return string
	 */
	public function dateRelative($value, $value_compare = 'now')
	{
		$d1 = new \DateTime($value);
		$d2 = new \DateTime($value_compare);

		$diff = $d2->getTimestamp() - $d1->getTimestamp();
		if($diff < 0)
		{
			'';
		}
		$d = ($d1->diff($d2))->days;
		$dd = ((new \DateTime($d1->format('m/d/Y')))
			->diff(new \DateTime($d2->format('m/d/Y'))))->days;

		if($dd > $d)
		{
			$d = $dd;
		}

		if($d == 0 || $diff < 86400)
		{
			switch(true)
			{
				case $diff < 60: // now
					return 'Just now';
					break;

				case $diff < 120: // 1 min ago
					return '1 minute ago';
					break;

				case $diff < 3600: // mins ago
					return sprintf('%d minutes ago', $diff / 60);
					break;

				case $diff < 7200: // 1 hr ago
					return '1 hour ago';
					break;

				case $diff < 86400: // hrs ago
					return sprintf('%d hours ago', $diff / 3600);
					break;
			}
		}

		switch(true)
		{
			case $d == 1: // yesterday
				return 'Yesterday';
				break;

			case $d < 7: // days ago
				return sprintf('%d days ago', $d);
				break;

			case $d < 31 && $d: // weeks ago
				$v = $d / 7;
				if($v < 2)
				{
					return '1 week ago';
				}
				else if($v >= 4)
				{
					return '1 month ago';
				}
				return sprintf('%d weeks ago', $v);
				break;

			case $d < 365: // months ago
				$v = $d / 30;
				if($v < 2)
				{
					return '1 month ago';
				}
				else if($v >= 12)
				{
					return '1 year ago';
				}
				return sprintf('%d months ago', $v);
				break;

			default:
				$v = $d / 365;
				if($v < 2)
				{
					return '1 year ago';
				}
				return sprintf('%d years ago', $v);
				break;
		}
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
	 * Format password using safe one-way hashing algorithm (use with Validate::password())
	 *
	 * @param string $password
	 * @param int $algorithm
	 * @return string
	 */
	public function password($password, $algorithm = \PASSWORD_BCRYPT)
	{
		return password_hash($password, $algorithm);
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
	 * @param float $time_elapsed (seconds, ex: microtime(true) - $start)
	 * @param array $characters
	 * @param bool $auto_trim_singulars
	 * @return string (ex: '1h 35m 55s')
	 */
	public function timeElapsed($time_elapsed, array $characters = ['%d years', '%d weeks',
		'%d days', '%d hours', '%d minutes', '%.4f seconds', '%g seconds'],
		$auto_trim_singulars = true)
	{
		$time_elapsed = (float)$time_elapsed;
		if(!$time_elapsed)
		{
			return sprintf($characters[6], $time_elapsed);
		}

		$x = floor($time_elapsed);
		$f = $time_elapsed - $x;
		$b = [
			$characters[0] => $time_elapsed / 31556926 % 12,
			$characters[1] => $time_elapsed / 604800 % 52,
			$characters[2] => $time_elapsed / 86400 % 7,
			$characters[3] => $time_elapsed / 3600 % 24,
			$characters[4] => $time_elapsed / 60 % 60
		];

		if($f)
		{
			$b[$characters[5]] = $time_elapsed % 60 + $f;
		}
		else
		{
			$b[$characters[6]] = $time_elapsed % 60;
		}

		$out = [];
		foreach($b as $k => $v)
		{
			if($v > 0)
			{
				if($auto_trim_singulars && $v == 1)
				{
					$k = rtrim($k, 's');
				}

				$out[] = sprintf($k, $v);
			}
		}

		return implode(' ', $out);
	}
}
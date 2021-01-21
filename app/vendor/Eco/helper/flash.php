<?php
/**
 * Eco is a PHP Framework for PHP 5.5+
 *
 * @package Eco
 * @copyright 2015-2021 Shay Anderson <https://www.shayanderson.com>
 * @license MIT License <https://github.com/shayanderson/eco/blob/master/LICENSE>
 * @link <https://github.com/shayanderson/eco>
 */

use Eco\System;

/**
 * Flash helper functions
 */

/**
 * Flash alert setter
 *
 * @param string $message
 * @return void
 */
function flash_alert($message)
{
	System::flash()->set('alert', $message);
}

/**
 * Flash alert getter
 *
 * @return string
 */
function flash_alert_get()
{
	return System::flash()->get('alert');
}

/**
 * Flash error setter
 *
 * @param string $message
 * @return void
 */
function flash_error($message)
{
	System::flash()->set('error', $message);
}

/**
 * Flash error getter
 *
 * @return string
 */
function flash_error_get()
{
	return System::flash()->get('error');
}
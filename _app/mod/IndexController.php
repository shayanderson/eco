<?php
/**
 * Eco is a PHP Micro-Framework for PHP 5.5+
 *
 * @package Eco
 * @copyright 2015-2016 Shay Anderson <http://www.shayanderson.com>
 * @license MIT License <https://github.com/shayanderson/eco/blob/master/LICENSE>
 * @link <https://github.com/shayanderson/eco>
 */

/**
 * Index controller
 */
class IndexController
{
	/**
	 * Home action
	 */
	public function home()
	{
		view()->set('status', 'ready');
		view('home');
	}
}
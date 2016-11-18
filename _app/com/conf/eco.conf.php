<?php
/**
 * Eco is a PHP Framework for PHP 5.5+
 *
 * @package Eco
 * @copyright 2015-2016 Shay Anderson <http://www.shayanderson.com>
 * @license MIT License <https://github.com/shayanderson/eco/blob/master/LICENSE>
 * @link <https://github.com/shayanderson/eco>
 */

use \Eco\System;

/**
 * Eco configuration settings
 */
return [
	'__eco__' => [

		/**
		 * Log settings
		 */
		'log' => [

			/**
			 * Sets what errors are logged (default: 2)
			 *	1 - only server errors are logged (500 errors)
			 *	2 - all errors are logged (403, 404, 500)
			 *	3 - no errors are logged
			 */
			'error_level' => 2,

			/**
			 * Sets what errors are sent to local system log writer (default: 1)
			 *	1 - only server errors are logged (500 errors)
			 *	2 - all errors are logged (403, 404, 500)
			 *	3 - no errors are logged
			 */
			'error_write_level' => 1,

			/**
			 * Set what level of log messages are logged (default: 4)
			 *	1 - error (error messages)
			 *	2 - warning (warnings and above)
			 *	3 - notice (notices and above)
			 *	4 - debug (all messages)
			 *	5 - none (no messages)
			 */
			'level' => 4
		],

		/**
		 * Path settings
		 */
		'path' => [

			/**
			 * Path where controller files are loaded from
			 */
			'controller' => PATH_MODULE,

			/**
			 * Path where view template files are loaded from
			 */
			'template' => PATH_TEMPLATE
		],

		/**
		 * Request settings
		 */
		'request' => [

			/**
			 * Auto sanitize request params GET and POST (default: true)
			 */
			'sanitize_params' => true
		]
	]
];
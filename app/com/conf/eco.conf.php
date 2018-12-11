<?php
/**
 * Eco configuration settings
 */
return [
	'_eco' => [

		/**
		 * Cache settings (global)
		 */
		'cache' => [

			/**
			 * Use compression, requires ZLIB functions (default: false)
			 */
			'compression' => false,

			/**
			 * Use encoding (default: false)
			 */
			'encoding' => false,

			/**
			 * Expire time string, example: '30 seconds' or 0 for no expire (default: 0)
			 */
			'expire' => 0,

			/**
			 * Use metadata (default: false)
			 */
			'metadata' => false,

			/**
			 * Cache path (default: PATH_APP . 'var' . DIRECTORY_SEPARATOR . 'cache')
			 */
			'path' => PATH_APP . 'var' . DIRECTORY_SEPARATOR . 'cache',

			/**
			 * Use serialization (default: true)
			 */
			'serialize' => true
		],

		/**
		 * Database settings
		 */
		'database' => [

			/**
			 * Database connections
			 */
			'connection' => [

				/**
				 * Default connection
				 */
				1 => [

					/**
					 * Database host
					 */
					'host' => 'localhost',

					/**
					 * Database name
					 */
					'database' => '',

					/**
					 * Database user
					 */
					'user' => '',

					/**
					 * Database password
					 */
					'password' => '',

					/**
					 * Enable query logging for debugging (default: false)
					 */
					'log' => false
				]
			],

			/**
			 * Makes fetch queries memory safe by auto appending LIMIT clause if does not exist
			 * use 0 (zero) for no limit (default: 10000)
			 */
			'global_limit' => 10000,

			/**
			 * Global database pagination settings
			 */
			'pagination' => [

				/**
				 * Records per page (default: 30)
				 */
				'rpp' => 30,

				/**
				 * Page settings
				 */
				'page' => [

					/**
					 * Encode page number in query string (default: true)
					 */
					'encode' => true,

					/**
					 * GET parameter name for current page number (default: 'pg')
					 */
					'get_var' => 'pg',

					/**
					 * Show page range count, 0 (zero) for no page range (default: 5)
					 */
					'range_count' => 5,
				],

				/**
				 * Markup wrappers
				 */
				'wrapper' => [

					/**
					 * Pagination group wrapper (default: '<div>{$group}</div>')
					 */
					'group' => '<div>{$group}</div>',

					/**
					 * Next page wrapper (default: '<a href="{$uri}">Next</a>')
					 */
					'next' => '<a href="{$uri}">Next</a>',

					/**
					 * Previous page wrapper (default: '<a href="{$uri}">Previous</a>')
					 */
					'prev' => '<a href="{$uri}">Previous</a>',

					/**
					 * Range page number wrapper (default: '<a href="{$uri}">{$page}</a>')
					 */
					'range' => '<a href="{$uri}">{$page}</a>',

					/**
					 * Active range page number wrapper (default: '{$page}')
					 */
					'range_active' => '{$page}'
				]
			]
		],

		/**
		 * Log settings
		 */
		'log' => [

			/**
			 * Default log category for database object (default: 'Eco\Database')
			 */
			'category_database' => 'Eco\Database',

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
			'level' => 4,

			/**
			 * Use error prefix in log 'Error (<code>): <message>' (default: true)
			 */
			'prefix_error' => true
		],

		/**
		 * Path settings
		 */
		'path' => [

			/**
			 * Path where controller files are loaded from (default: PATH_MODULE)
			 */
			'controller' => PATH_MODULE,

			/**
			 * Path where view template files are loaded from (default: PATH_TEMPLATE)
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
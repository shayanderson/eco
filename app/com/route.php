<?php
/**
 * Application routes
 */

// set routes
return [
	'/' => 'IndexController->home',
	// error routes
	eco::ERROR_FORBIDDEN => function() { view('_global/error/403'); },
	eco::ERROR_NOT_FOUND => function() { view('_global/error/404'); },
	eco::ERROR_SERVER => function() { view('_global/error/500'); }
];
<?php
/**
 * Application bootstrap
 */

// set Eco access class
class eco extends \Eco\System {}

// class autoloading
eco::autoload([
	PATH_LIB,
	PATH_LIB . 'vendor'
]);

// configure settings
eco::configure([
	// eco::CONF_LOG_ERROR_LEVEL => eco::ERROR_LOG_ALL, // default
	// eco::CONF_LOG_ERROR_WRITE_LEVEL => eco::ERROR_LOG_SERVER, // default
	eco::CONF_LOG_LEVEL => eco::LOG_DEBUG, // default: eco::LOG_ERROR
	eco::CONF_PATH => PATH_MODULE,
	eco::CONF_PATH_TEMPLATE => PATH_TEMPLATE,
	// eco::CONF_SANITIZE_REQUEST_PARAMS => true // default
]);

// load helper functions (optional)
require_once PATH_LIB . 'vendor/Eco/helper/eco.php';
require_once PATH_LIB . 'vendor/Eco/helper/alias.php';
require_once PATH_LIB . 'vendor/Eco/helper/factory.php';
require_once PATH_LIB . 'vendor/Eco/helper/flash.php';
require_once PATH_LIB . 'vendor/Eco/helper/redirect.php';
require_once PATH_LIB . 'vendor/Eco/helper/request.php';
require_once PATH_LIB . 'vendor/Eco/helper/view.php';

// set routes
eco::route([
	'/' => 'IndexController->home',
	// error routes
	eco::ERROR_FORBIDDEN => 'ErrorController->error403',
	eco::ERROR_NOT_FOUND => 'ErrorController->error404',
	eco::ERROR_SERVER => 'ErrorController->error500'
]);

// set hook to display log
eco::hook(eco::HOOK_AFTER, function() {
	echo '<pre>' . print_r(eco::log()->get(), true) . '</pre>';
});
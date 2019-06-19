<?php
/**
 * Application bootstrap
 */

// set Eco access class
class eco extends \Eco\System {}

// class autoloading
eco::autoload([
	PATH_LIB,
	PATH_VENDOR
]);

// benchmark start point
\Eco\Benchmark::start();

// load Eco configuration settings
eco::conf(PATH_CONF . 'eco.conf.php');

// load core functions
require_once PATH_VENDOR . 'Eco/helper/eco.php';

// load helper functions (optional)
require_once PATH_VENDOR . 'Eco/helper/alias.php';
// require_once PATH_VENDOR . 'Eco/helper/factory.php';
require_once PATH_VENDOR . 'Eco/helper/flash.php';
require_once PATH_VENDOR . 'Eco/helper/request.php';
require_once PATH_VENDOR . 'Eco/helper/response.php';
// require_once PATH_VENDOR . 'Eco/helper/view.php';

// load routes
eco::route(require PATH_COM . 'route.php');

// set hook to display log
eco::hook(eco::HOOK_AFTER, function() {
	echo '<pre>' . print_r(array_merge(eco::log()->get(), \Eco\Benchmark::getPoints(true)), true)
		. '</pre>';
});
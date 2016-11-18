<?php

// paths
define('PATH_ROOT', __DIR__ . DIRECTORY_SEPARATOR);
define('PATH_APP' , PATH_ROOT . '_app' . DIRECTORY_SEPARATOR);
define('PATH_COM', PATH_APP . 'com' . DIRECTORY_SEPARATOR);
define('PATH_CONF', PATH_COM . 'conf' . DIRECTORY_SEPARATOR);
define('PATH_LIB', PATH_APP . 'lib' . DIRECTORY_SEPARATOR);
define('PATH_MODULE', PATH_APP . 'mod' . DIRECTORY_SEPARATOR);
define('PATH_TEMPLATE', PATH_APP . 'tpl' . DIRECTORY_SEPARATOR);
define('PATH_TEMPLATE_GLOBAL', PATH_TEMPLATE . '_global' . DIRECTORY_SEPARATOR);

// load Eco
require_once PATH_LIB . 'vendor/Eco/System.php';

// application bootstrap
require_once PATH_COM . 'app.bootstrap.php';

try
{
	// run application
	eco::run();
}
catch(Exception $ex)
{
	// handle application exception
	eco::error('Exception occurred: ' . $ex->getMessage());
}
<?php

// paths
define('PATH_ROOT', __DIR__ . DIRECTORY_SEPARATOR);
define('PATH_APP' , PATH_ROOT . 'app' . DIRECTORY_SEPARATOR);
define('PATH_COM', PATH_APP . 'com' . DIRECTORY_SEPARATOR);
define('PATH_CONF', PATH_COM . 'conf' . DIRECTORY_SEPARATOR);
define('PATH_LIB', PATH_APP . 'lib' . DIRECTORY_SEPARATOR);
define('PATH_MODULE', PATH_APP . 'mod' . DIRECTORY_SEPARATOR);
define('PATH_TEMPLATE', PATH_APP . 'tpl' . DIRECTORY_SEPARATOR);
define('PATH_TEMPLATE_GLOBAL', PATH_TEMPLATE . '_global' . DIRECTORY_SEPARATOR);
define('PATH_VENDOR', PATH_APP . 'vendor' . DIRECTORY_SEPARATOR);

// load Eco
require_once PATH_VENDOR . 'Eco/System.php';

// application bootstrap
require_once PATH_COM . 'bootstrap.php';

try
{
	// run application
	eco::run();
}
catch(Throwable $th)
{
	// handle application error
	eco::error($th->getMessage());
}
catch(Exception $ex)
{
	// handle application exception
	eco::error($ex->getMessage());
}
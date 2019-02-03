<?php
error_reporting ( E_ALL );
include 'c3.php';
if (! defined ( 'DS' )) {
	define ( 'DS', DIRECTORY_SEPARATOR );
	define ( 'ROOT', __DIR__ . DS . 'app' . DS );
	define ( 'MY_APP_STARTED', true );
}
$config = include ROOT . 'config/config.php';
$config ["siteUrl"] = "http://dev.local/";
$config ['sessionName'] = '';
require ROOT . './../vendor/autoload.php';
require ROOT . 'config/services.php';
\Ubiquity\controllers\Router::get ( "c3/(.*?)", function () {
	require ROOT . './../c3.php';
} );
\Ubiquity\controllers\Startup::run ( $config );

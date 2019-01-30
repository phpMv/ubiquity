<?php
error_reporting ( E_ALL );
define ( 'DS', DIRECTORY_SEPARATOR );
define ( 'ROOT', __DIR__ . DS . 'app' . DS );
include 'c3.php';
define ( 'MY_APP_STARTED', true );
$config = include ROOT . 'config/config.php';
$config ["siteUrl"] = "http://dev.local/prj-test-admin/";
$config ['sessionName'] = '';
require ROOT . './../vendor/autoload.php';
require ROOT . 'config/services.php';
\Ubiquity\controllers\Router::get ( "c3/(.*?)", function () {
	require ROOT . './../c3.php';
} );
\Ubiquity\controllers\Startup::run ( $config );

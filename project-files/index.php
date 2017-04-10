<?php
use micro\controllers\Startup;
use micro\controllers\Autoloader;
use micro\orm\DAO;
use micro\orm\OrmUtils;
error_reporting(E_ALL);

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', realpath('app').DS);

$config=include_once ROOT.'config.php';

require_once ROOT.'micro/controllers/Autoloader.php';
require_once ROOT.'./../vendor/autoload.php';

Autoloader::register($config);
//uncomment for logging or tests
//\micro\log\Logger::init();
//$config["siteUrl"]="http://127.0.0.1:8090/";

$db=$config["database"];
if($db["dbName"]!==""){
	DAO::connect($db["dbName"],@$db["serverName"],@$db["port"],@$db["user"],@$db["password"]);
	OrmUtils::startOrm($config);
}
Startup::run($config,$_GET["c"]);

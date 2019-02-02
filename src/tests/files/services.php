<?php
use Ubiquity\controllers\Router;

\Ubiquity\cache\CacheManager::startProd ( $config );
$db = $config ["database"];
if ($db ["dbName"] !== "") {
	\Ubiquity\orm\DAO::connect ( $db ["type"], $db ["dbName"], @$db ["serverName"], @$db ["port"], @$db ["user"], @$db ["password"], @$db ["options"], @$db ["cache"] );
}
Router::startAll ();
Router::addRoute ( "_default", "controllers\\IndexController" );
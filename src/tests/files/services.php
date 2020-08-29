<?php
use Ubiquity\controllers\Router;
$config ["debug"] = true;
\Ubiquity\log\Logger::init ( $config );
\Ubiquity\cache\CacheManager::startProd ( $config );
\Ubiquity\orm\DAO::setModelsDatabases ( [ "models\\bench\\Fortune" => "bench","models\\bench\\World" => "bench","models\\Groupe" => "default","models\\Organization" => "default","models\\Organizationsettings" => "default","models\\Settings" => "default","models\\User" => "default" ] );
Router::startAll ();
Router::addRoute ( "_default", "controllers\\IndexController" );
\Ubiquity\assets\AssetsManager::start ( $config );
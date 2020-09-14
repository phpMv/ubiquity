<?php
use Ubiquity\controllers\Router;
$config ["debug"] = true;
\Ubiquity\log\Logger::init ( $config );
\Ubiquity\cache\CacheManager::startProd ( $config );
\Ubiquity\orm\DAO::start ();
Router::startAll ();
Router::addRoute ( "_default", "controllers\\IndexController" );
Router::addCallableRoute('/call/hello', function(){echo 'Hello world!';},['get']);
\Ubiquity\assets\AssetsManager::start ( $config );
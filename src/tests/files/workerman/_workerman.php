#!/usr/bin/env php
<?php
// workerman.php
if (! defined ( 'DS' )) {
	define ( 'DS', DIRECTORY_SEPARATOR );
	define ( 'ROOT', __DIR__ . \DS . '..' . \DS . 'app' . \DS );
}
$config = include ROOT . 'config/config.php';
$sConfig = [ 'host' => 'dev.local','port' => 8095,'sessionName' => 'workerman' ];
$config ["sessionName"] = $sConfig ["sessionName"];
$address = $sConfig ['host'] . ':' . $sConfig ['port'];
$config ["siteUrl"] = 'http://' . $address;
require ROOT . './../vendor/autoload.php';
require ROOT . 'config/workerServices.php';
$workerServer = new \Ubiquity\servers\workerman\WorkermanServer ();
$workerServer->init ( $config, __DIR__ );
$workerServer->setDefaultCount ();
$workerServer->daemonize ();
$workerServer->run ( $sConfig ['host'], $sConfig ['port'], $sConfig ['socket'] ?? [ ]);

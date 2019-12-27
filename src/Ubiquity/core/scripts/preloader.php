<?php
define ( 'APP_ROOT', realpath ( __DIR__ . '/../../../../../../../app/' ) );
include_once __DIR__ . './../../cache/Preloader.php';
$config = include \APP_ROOT . './config/preloader-config.php';
\Ubiquity\cache\Preloader::fromArray ( \APP_ROOT, $config );

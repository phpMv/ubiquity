<?php
define ( 'DS', DIRECTORY_SEPARATOR );
define ( 'ROOT', __DIR__ . DS . 'app' . DS );
require ROOT . './../vendor/autoload.php';
use Ubiquity\utils\base\UFileSystem;
echo UFileSystem::load ( ROOT . 'config/services.php' );
echo "*************************************************";
echo UFileSystem::load ( ROOT . '../.htaccess' );

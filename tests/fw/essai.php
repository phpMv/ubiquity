<?php
include 'Ubiquity/utils/base/UFileSystem.php';
use Ubiquity\utils\base\UFileSystem;
echo UFileSystem::load ( ROOT . 'config/services.php' );
echo "*************************************************";
echo UFileSystem::load ( ROOT . '../.htaccess' );

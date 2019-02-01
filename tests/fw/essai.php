<?php
include 'Ubiquity/utils/base/UFileSystem.php';
include 'Ubiquity/utils/base/traits/UFileSystemWriter.php';
use Ubiquity\utils\base\UFileSystem;
echo UFileSystem::load ( ROOT . 'config/services.php' );
echo "*************************************************";
echo UFileSystem::load ( ROOT . '../.htaccess' );

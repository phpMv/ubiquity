<?php
use Ubiquity\utils\base\UFileSystem;
$str = UFileSystem::load ( ROOT . 'config/services.php' );
echo $str;

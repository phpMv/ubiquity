<?php
$uri = ltrim(urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)),'/');
if (!file_exists(__DIR__ . '/' .$uri)) {
	$_GET['c'] = $uri;
}else{
	$_GET['c']='';
}
return false;
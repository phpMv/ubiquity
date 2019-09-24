<?php
use tests\unit\controllers\services\Service;

require_once 'tests/unit/controllers/services/Service.php';

if (! defined ( 'ROOT' )) {
	define ( 'DS', DIRECTORY_SEPARATOR );
	define ( 'ROOT', __DIR__ . DS . 'app' . DS );
}
if (! defined ( 'DB_SERVER' )) {
	$ip = getenv ( 'SERVICE_MYSQL_IP' );
	if ($ip === false) {
		$ip = '127.0.0.1';
	}
	define ( 'DB_SERVER', $ip );
}
$_GET ["c"] = "TestController";
return array (
			"siteUrl" => "http://127.0.0.1/",
			"database" => [
							'default' => array ("type" => "mysql","dbName" => "messagerie","serverName" => DB_SERVER,"port" => 3306,"user" => "root","password" => "","options" => array (),"cache" => false ),
							'mysqli' => array ("wrapper" => "\\Ubiquity\\db\\providers\\mysqli\\MysqliWrapper","type" => "mysql","dbName" => "messagerie","serverName" => DB_SERVER,"port" => 3306,"user" => "root","password" => "","options" => array (),"cache" => false ) ],
			"namespaces" => array (),
			"sessionName" => "messagerie",
			"test" => false,
			"debug" => true,
			"di" => array ("injected" => function ($controller) {
				return new Service ( $controller );
			} ),
			"cache" => array ("directory" => "cache/","system" => "Ubiquity\\cache\\system\\ArrayCache","params" => array () ),
			"mvcNS" => array ("models" => "models","controllers" => "tests\\unit\\controllers\\controllers","rest" => "rest" ),
			"isRest" => function () {
				return \Ubiquity\utils\http\URequest::getUrlParts () [0] === "rest";
			} );
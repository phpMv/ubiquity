<?php
return array (
			"siteUrl" => "http://dev.local/",
			"database" => [
							'default' => array ("type" => "mysql","dbName" => "messagerie","serverName" => "127.0.0.1","port" => 3306,"user" => "root","password" => "","options" => array (),"cache" => false ),
							'mysqli' => array ("wrapper" => "\\Ubiquity\\db\\providers\\mysqli\\MysqliWrapper","type" => "mysql","dbName" => "messagerie","serverName" => "127.0.0.1","port" => 3306,"user" => "root","password" => "","options" => array (),"cache" => false ) ],
			"sessionName" => "verif",
			"namespaces" => array (),
			"templateEngine" => "Ubiquity\\views\\engine\\Twig",
			"templateEngineOptions" => array ("cache" => false,"activeTheme" => "semantic" ),
			"test" => false,
			"debug" => true,
			"logger" => function () {
				return new \Ubiquity\log\libraries\UMonolog ( "verif", \Monolog\Logger::INFO );
			},
			"di" => array ("*.allS" => function ($controller) {
				return new \services\IAllService ();
			},"*.inj" => function ($ctrl) {
				return new \services\IAllService ();
			},"@exec" => array ("jquery" => function ($controller) {
				return \Ubiquity\core\Framework::diSemantic ( $controller );
			} ) ),
			"cache" => array ("directory" => "cache/","system" => "Ubiquity\\cache\\system\\ArrayCache","params" => array () ),
			"mvcNS" => array ("models" => "models","controllers" => "controllers","rest" => "" ),
			"isRest" => function () {
				return \Ubiquity\utils\http\URequest::getUrlParts () [0] === "rest";
			} );
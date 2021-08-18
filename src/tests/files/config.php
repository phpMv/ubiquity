<?php

return array (
				"siteUrl" => "http://dev.local/",
				"database" => [
									'default' => [ "type" => "mysql","dbName" => "messagerie","serverName" => "127.0.0.1","port" => 3306,"user" => "root","password" => "","options" => array (),"cache" => false ],
									'mysqli' => [ "wrapper" => "\\Ubiquity\\db\\providers\\mysqli\\MysqliWrapper","type" => "mysql","dbName" => "messagerie","serverName" => "127.0.0.1","port" => 3306,"user" => "root","password" => "","options" => array (),"cache" => false ],
									'bench' => [ "wrapper" => "\\Ubiquity\\db\\providers\\pdo\\PDOWrapper","type" => "mysql","dbName" => "hello_world","serverName" => "127.0.0.1","port" => 3306,"user" => "root","password" => "","options" => [ ],"cache" => false ] ],
				"sessionName" => "verif",
				"namespaces" => array (),
				"templateEngine" => "Ubiquity\\views\\engine\\Twig",
				"templateEngineOptions" => array ("cache" => false,"activeTheme" => "semantic" ),
				"test" => false,
				"debug" => true,
				"logger" => function () {
					return new \Ubiquity\log\libraries\UMonolog ( "verif", \Monolog\Logger::INFO );
				},
				"di" => array ("*.allS" =>
					function ($controller=null) {
						if(!($controller instanceof \Ubiquity\controllers\rest\RestBaseController)){
							return new \services\IAllService ($controller);
						}
					}
				,"*.inj" =>
					function ($ctrl=null) {
						if(!($ctrl instanceof \Ubiquity\controllers\rest\RestBaseController)){
							return new \services\IAllService ($ctrl);
						}
					}
				,"@exec" => array ("jquery" =>
					function ($controller) {
						return \Ubiquity\core\Framework::diSemantic ( $controller );
					}
				) ),
				"cache" => array ("directory" => "cache/","system" => "Ubiquity\\cache\\system\\ArrayCache","params" => array () ),
				"mvcNS" => array ("models" => "models","controllers" => "controllers","rest" => "" ),
				"isRest" => function () {
					return \Ubiquity\utils\http\URequest::getUrlParts () [0] === "rest";
				} );
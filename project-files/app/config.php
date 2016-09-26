<?php
return array(
		"siteUrl"=>"%siteUrl%",
		"documentRoot"=>"%documentRoot%",
		"database"=>[
				"serverName"=>"127.0.0.1",
				"port"=>"3306",
				"user"=>"root",
				"password"=>""
		],
		"onStartup"=>function($action){
		},
		"directories"=>[],
		"templateEngine"=>'micro\views\engine\Twig',
		"templateEngineOptions"=>array("cache"=>false),
		"test"=>false
);

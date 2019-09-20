<?php
return array (
			"/route/test/(index/)?" => array ("controller" => "controllers\\TestControllerWithControl","action" => "index","parameters" => [ ],"name" => "test-route-index" ),
			"/route/test/ctrl/" => array ("controller" => "controllers\\TestControllerWithControl","action" => "actionWithControl","parameters" => [ ],"name" => "test-route-ctrl" ),
			"/route/test/params/(.+?)/(.*?)" => array ("controller" => "controllers\\TestControllerWithControl","action" => "withParams","parameters" => array (false,"~1" ),"name" => "test-route-params","cache" => false,"duration" => false ),
			"/react/test/(index/)?" => array ("controller" => "controllers\\TestReactController","action" => "index","parameters" => array (),"name" => "TestReactController-index","cache" => false,"duration" => false ),
			"/react/get/" => array ("controller" => "controllers\\TestReactController","action" => "testGet","parameters" => array (),"name" => "TestReactController-testGet","cache" => false,"duration" => false ),
			"/react/set/session/" => array ("controller" => "controllers\\TestReactController","action" => "testSetSession","parameters" => array (),"name" => "TestReactController-testSetSession","cache" => false,"duration" => false ),
			"/react/get/session/" => array ("controller" => "controllers\\TestReactController","action" => "testGetSession","parameters" => array (),"name" => "TestReactController-testGetSession","cache" => false,"duration" => false ) );

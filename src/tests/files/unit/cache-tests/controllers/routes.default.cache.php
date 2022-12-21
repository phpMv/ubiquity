<?php
return array(
	"/route/test/(index/)?" => array("controller" => "controllers\\TestControllerWithControl", "action" => "index", "parameters" => [], "name" => "test-route-index"),
	"/route/test/ctrl/" => array("controller" => "controllers\\TestControllerWithControl", "action" => "actionWithControl", "parameters" => [], "name" => "test-route-ctrl"),
	"/route/test/params/(.+?)/(.*?)" => array("controller" => "controllers\\TestControllerWithControl", "action" => "withParams", "parameters" => array(false, "~1"), "name" => "test-route-params", "cache" => false, "duration" => false),
	"/react/test/(index/)?" => array("controller" => "controllers\\TestReactController", "action" => "index", "parameters" => array(), "name" => "TestReactController-index", "cache" => false, "duration" => false),
	"/react/get/" => array("controller" => "controllers\\TestReactController", "action" => "testGet", "parameters" => array(), "name" => "TestReactController-testGet", "cache" => false, "duration" => false),
	"/react/set/session/" => array("controller" => "controllers\\TestReactController", "action" => "testSetSession", "parameters" => array(), "name" => "TestReactController-testSetSession", "cache" => false, "duration" => false),
	"/react/get/session/" => array("controller" => "controllers\\TestReactController", "action" => "testGetSession", "parameters" => array(), "name" => "TestReactController-testGetSession", "cache" => false, "duration" => false),
	"/users/(\\d+)/" => ["get" => ["controller" => "controllers\\UsersController", "action" => "byId", "parameters" => [0], "name" => "users.byId", "cache" => false, "duration" => 0]], "/users/(.+?)/" => ["get" => ["controller" => "controllers\\UsersController", "action" => "one", "parameters" => [0], "name" => "users.one", "cache" => false, "duration" => 0]], "/test/" => ["get" => ["controller" => "controllers\\Test", "action" => "index", "parameters" => [], "name" => "test", "cache" => false, "duration" => 0]], "/users/(index/)?" => ["get" => ["controller" => "controllers\\UsersController", "action" => "index", "parameters" => [], "name" => "users.index", "cache" => false, "duration" => 0]]
);

<?php
return array ("/rest/test/" => array ("controller" => "tests\\unit\\controllers\\controllers\\TestRestController","action" => "index","parameters" => [ ],"name" => "test-rest-route" ),"/rest/test/ticket/" => array ("controller" => "tests\\unit\\controllers\\controllers\\TestRestController","action" => "withTicket","parameters" => [ ],"name" => "test-rest-route-ticket" ),
		"/rest/test/connect/" => array ("controller" => "tests\\unit\\controllers\\controllers\\TestRestController","action" => "connect","parameters" => [ ],"name" => "test-rest-route-connect" ) );

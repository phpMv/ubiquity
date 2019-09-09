<?php

return array(
  '#namespace' => 'controllers',
  '#uses' => array (
  'RestController' => 'Ubiquity\\controllers\\rest\\RestController',
),
  '#traitMethodOverrides' => array (
  'controllers\\TestRestController' => 
  array (
  ),
),
  'controllers\\TestRestController' => array(
    array('#name' => 'route', '#type' => 'Ubiquity\\annotations\\router\\RouteAnnotation', "/rest/test","inherited"=>false,"automated"=>false),
    array('#name' => 'rest', '#type' => 'Ubiquity\\annotations\\rest\\RestAnnotation', "resource"=>"")
  ),
  'controllers\\TestRestController::index' => array(
    array('#name' => 'route', '#type' => 'Ubiquity\\annotations\\router\\RouteAnnotation', "cache"=>false)
  ),
);


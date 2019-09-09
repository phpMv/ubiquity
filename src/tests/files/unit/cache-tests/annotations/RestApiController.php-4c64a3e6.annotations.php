<?php

return array(
  '#namespace' => 'controllers',
  '#uses' => array (
  'NormalizersManager' => 'Ubiquity\\contents\\normalizers\\NormalizersManager',
  'RestController' => 'Ubiquity\\controllers\\rest\\RestController',
  'EventsManager' => 'Ubiquity\\events\\EventsManager',
  'DefineLocaleEventListener' => 'eventListener\\DefineLocaleEventListener',
  'User' => 'models\\User',
  'UserNormalizer' => 'normalizer\\UserNormalizer',
  'Organization' => 'models\\Organization',
  'OrgaNormalizer' => 'normalizer\\OrgaNormalizer',
  'DAO' => 'Ubiquity\\orm\\DAO',
),
  '#traitMethodOverrides' => array (
  'controllers\\RestApiController' => 
  array (
  ),
),
  'controllers\\RestApiController' => array(
    array('#name' => 'route', '#type' => 'Ubiquity\\annotations\\router\\RouteAnnotation', "/rest/benchmark","inherited"=>false,"automated"=>false),
    array('#name' => 'rest', '#type' => 'Ubiquity\\annotations\\rest\\RestAnnotation', "resource"=>"")
  ),
  'controllers\\RestApiController::index' => array(
    array('#name' => 'route', '#type' => 'Ubiquity\\annotations\\router\\RouteAnnotation', "cache"=>false)
  ),
);


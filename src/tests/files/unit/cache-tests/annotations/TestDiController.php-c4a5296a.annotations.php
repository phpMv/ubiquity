<?php

return array(
  '#namespace' => 'controllers',
  '#uses' => array (
  'IService' => 'services\\IService',
  'Controller' => 'Ubiquity\\controllers\\Controller',
  'IInjected' => 'services\\IInjected',
  'CacheManager' => 'Ubiquity\\cache\\CacheManager',
  'Startup' => 'Ubiquity\\controllers\\Startup',
  'RestException' => 'Ubiquity\\exceptions\\RestException',
),
  '#traitMethodOverrides' => array (
  'controllers\\TestDiController' => 
  array (
  ),
),
  'controllers\\TestDiController' => array(
    array('#name' => 'property', '#type' => 'mindplay\\annotations\\standard\\PropertyAnnotation', 'type' => '\\services\\IAllService', 'name' => 'llS')
  ),
  'controllers\\TestDiController::$iService' => array(
    array('#name' => 'autowired', '#type' => 'Ubiquity\\annotations\\di\\AutowiredAnnotation'),
    array('#name' => 'var', '#type' => 'mindplay\\annotations\\standard\\VarAnnotation', 'type' => 'IService')
  ),
  'controllers\\TestDiController::$inj' => array(
    array('#name' => 'injected', '#type' => 'Ubiquity\\annotations\\di\\InjectedAnnotation'),
    array('#name' => 'var', '#type' => 'mindplay\\annotations\\standard\\VarAnnotation', 'type' => 'IInjected')
  ),
);


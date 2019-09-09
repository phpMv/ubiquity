<?php

return array(
  '#namespace' => 'Ubiquity\\controllers\\rest',
  '#uses' => array (
  'CacheManager' => 'Ubiquity\\cache\\CacheManager',
  'DAO' => 'Ubiquity\\orm\\DAO',
),
  '#traitMethodOverrides' => array (
  'Ubiquity\\controllers\\rest\\RestController' => 
  array (
  ),
),
  'Ubiquity\\controllers\\rest\\RestController::get' => array(
    array('#name' => 'param', '#type' => 'mindplay\\annotations\\standard\\ParamAnnotation', 'type' => 'string', 'name' => 'condition'),
    array('#name' => 'param', '#type' => 'mindplay\\annotations\\standard\\ParamAnnotation', 'type' => 'boolean|string', 'name' => 'included'),
    array('#name' => 'param', '#type' => 'mindplay\\annotations\\standard\\ParamAnnotation', 'type' => 'boolean', 'name' => 'useCache'),
    array('#name' => 'route', '#type' => 'Ubiquity\\annotations\\router\\RouteAnnotation', "methods"=>["get"])
  ),
  'Ubiquity\\controllers\\rest\\RestController::getOne' => array(
    array('#name' => 'param', '#type' => 'mindplay\\annotations\\standard\\ParamAnnotation', 'type' => 'string', 'name' => 'keyValues'),
    array('#name' => 'param', '#type' => 'mindplay\\annotations\\standard\\ParamAnnotation', 'type' => 'boolean|string', 'name' => 'included'),
    array('#name' => 'param', '#type' => 'mindplay\\annotations\\standard\\ParamAnnotation', 'type' => 'boolean', 'name' => 'useCache'),
    array('#name' => 'route', '#type' => 'Ubiquity\\annotations\\router\\RouteAnnotation', "methods"=>["get"])
  ),
  'Ubiquity\\controllers\\rest\\RestController::update' => array(
    array('#name' => 'param', '#type' => 'mindplay\\annotations\\standard\\ParamAnnotation', 'type' => 'array', 'name' => 'keyValues'),
    array('#name' => 'authorization', '#type' => 'Ubiquity\\annotations\\rest\\AuthorizationAnnotation'),
    array('#name' => 'route', '#type' => 'Ubiquity\\annotations\\router\\RouteAnnotation', "methods"=>["patch"])
  ),
  'Ubiquity\\controllers\\rest\\RestController::add' => array(
    array('#name' => 'authorization', '#type' => 'Ubiquity\\annotations\\rest\\AuthorizationAnnotation'),
    array('#name' => 'route', '#type' => 'Ubiquity\\annotations\\router\\RouteAnnotation', "methods"=>["post"])
  ),
  'Ubiquity\\controllers\\rest\\RestController::delete' => array(
    array('#name' => 'param', '#type' => 'mindplay\\annotations\\standard\\ParamAnnotation', 'type' => 'array', 'name' => 'keyValues'),
    array('#name' => 'route', '#type' => 'Ubiquity\\annotations\\router\\RouteAnnotation', "methods"=>["delete"],"priority"=>30),
    array('#name' => 'authorization', '#type' => 'Ubiquity\\annotations\\rest\\AuthorizationAnnotation')
  ),
);


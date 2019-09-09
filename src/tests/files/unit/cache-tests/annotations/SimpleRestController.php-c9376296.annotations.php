<?php

return array(
  '#namespace' => 'Ubiquity\\controllers\\rest',
  '#uses' => array (
  'CacheManager' => 'Ubiquity\\cache\\CacheManager',
  'DAO' => 'Ubiquity\\orm\\DAO',
),
  '#traitMethodOverrides' => array (
  'Ubiquity\\controllers\\rest\\SimpleRestController' => 
  array (
  ),
),
  'Ubiquity\\controllers\\rest\\SimpleRestController::index' => array(
    array('#name' => 'route', '#type' => 'Ubiquity\\annotations\\router\\RouteAnnotation', "/links","methods"=>["get"],"priority"=>3000)
  ),
  'Ubiquity\\controllers\\rest\\SimpleRestController::getAll_' => array(
    array('#name' => 'route', '#type' => 'Ubiquity\\annotations\\router\\RouteAnnotation', "/","methods"=>["get"],"priority"=>0)
  ),
  'Ubiquity\\controllers\\rest\\SimpleRestController::getOne' => array(
    array('#name' => 'param', '#type' => 'mindplay\\annotations\\standard\\ParamAnnotation', 'type' => 'string', 'name' => 'id'),
    array('#name' => 'route', '#type' => 'Ubiquity\\annotations\\router\\RouteAnnotation', "{id}/","methods"=>["get"],"priority"=>1000)
  ),
  'Ubiquity\\controllers\\rest\\SimpleRestController::update' => array(
    array('#name' => 'param', '#type' => 'mindplay\\annotations\\standard\\ParamAnnotation', 'type' => 'array', 'name' => 'keyValues'),
    array('#name' => 'authorization', '#type' => 'Ubiquity\\annotations\\rest\\AuthorizationAnnotation'),
    array('#name' => 'route', '#type' => 'Ubiquity\\annotations\\router\\RouteAnnotation', "/{keyValues}","methods"=>["patch"],"priority"=>0)
  ),
  'Ubiquity\\controllers\\rest\\SimpleRestController::options' => array(
    array('#name' => 'route', '#type' => 'Ubiquity\\annotations\\router\\RouteAnnotation', "/","methods"=>["options"],"priority"=>3000)
  ),
  'Ubiquity\\controllers\\rest\\SimpleRestController::add' => array(
    array('#name' => 'authorization', '#type' => 'Ubiquity\\annotations\\rest\\AuthorizationAnnotation'),
    array('#name' => 'route', '#type' => 'Ubiquity\\annotations\\router\\RouteAnnotation', "/","methods"=>["post"],"priority"=>0)
  ),
  'Ubiquity\\controllers\\rest\\SimpleRestController::delete' => array(
    array('#name' => 'param', '#type' => 'mindplay\\annotations\\standard\\ParamAnnotation', 'type' => 'array', 'name' => 'keyValues'),
    array('#name' => 'route', '#type' => 'Ubiquity\\annotations\\router\\RouteAnnotation', "/{keyValues}","methods"=>["delete"],"priority"=>30),
    array('#name' => 'authorization', '#type' => 'Ubiquity\\annotations\\rest\\AuthorizationAnnotation')
  ),
);


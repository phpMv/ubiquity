<?php

return array(
  '#namespace' => 'Ubiquity\\controllers\\rest\\api\\jsonapi',
  '#uses' => array (
  'DAO' => 'Ubiquity\\orm\\DAO',
  'RestError' => 'Ubiquity\\controllers\\rest\\RestError',
  'Startup' => 'Ubiquity\\controllers\\Startup',
  'RestBaseController' => 'Ubiquity\\controllers\\rest\\RestBaseController',
  'URequest' => 'Ubiquity\\utils\\http\\URequest',
  'OrmUtils' => 'Ubiquity\\orm\\OrmUtils',
  'RestServer' => 'Ubiquity\\controllers\\rest\\RestServer',
  'CRUDHelper' => 'Ubiquity\\controllers\\crud\\CRUDHelper',
),
  '#traitMethodOverrides' => array (
  'Ubiquity\\controllers\\rest\\api\\jsonapi\\JsonApiRestController' => 
  array (
  ),
),
  'Ubiquity\\controllers\\rest\\api\\jsonapi\\JsonApiRestController::getRestServer' => array(
    array('#name' => 'return', '#type' => 'mindplay\\annotations\\standard\\ReturnAnnotation', 'type' => 'RestServer')
  ),
  'Ubiquity\\controllers\\rest\\api\\jsonapi\\JsonApiRestController::options' => array(
    array('#name' => 'route', '#type' => 'Ubiquity\\annotations\\router\\RouteAnnotation', "{resource}","methods"=>["options"],"priority"=>3000)
  ),
  'Ubiquity\\controllers\\rest\\api\\jsonapi\\JsonApiRestController::index' => array(
    array('#name' => 'route', '#type' => 'Ubiquity\\annotations\\router\\RouteAnnotation', "links/","methods"=>["get"],"priority"=>3000)
  ),
  'Ubiquity\\controllers\\rest\\api\\jsonapi\\JsonApiRestController::connect' => array(
    array('#name' => 'route', '#type' => 'Ubiquity\\annotations\\router\\RouteAnnotation', "connect/","priority"=>2500)
  ),
  'Ubiquity\\controllers\\rest\\api\\jsonapi\\JsonApiRestController::getAll_' => array(
    array('#name' => 'route', '#type' => 'Ubiquity\\annotations\\router\\RouteAnnotation', "{resource}/","methods"=>["get"],"priority"=>0)
  ),
  'Ubiquity\\controllers\\rest\\api\\jsonapi\\JsonApiRestController::getOne_' => array(
    array('#name' => 'param', '#type' => 'mindplay\\annotations\\standard\\ParamAnnotation', 'type' => 'string', 'name' => 'resource'),
    array('#name' => 'param', '#type' => 'mindplay\\annotations\\standard\\ParamAnnotation', 'type' => 'string', 'name' => 'id'),
    array('#name' => 'route', '#type' => 'Ubiquity\\annotations\\router\\RouteAnnotation', "{resource}/{id}/","methods"=>["get"],"priority"=>1000)
  ),
  'Ubiquity\\controllers\\rest\\api\\jsonapi\\JsonApiRestController::getRelationShip_' => array(
    array('#name' => 'param', '#type' => 'mindplay\\annotations\\standard\\ParamAnnotation', 'type' => 'string', 'name' => 'resource'),
    array('#name' => 'param', '#type' => 'mindplay\\annotations\\standard\\ParamAnnotation', 'type' => 'string', 'name' => 'id'),
    array('#name' => 'param', '#type' => 'mindplay\\annotations\\standard\\ParamAnnotation', 'type' => 'string', 'name' => 'member'),
    array('#name' => 'route', '#type' => 'Ubiquity\\annotations\\router\\RouteAnnotation', "{resource}/{id}/relationships/{member}/","methods"=>["get"],"priority"=>2000)
  ),
  'Ubiquity\\controllers\\rest\\api\\jsonapi\\JsonApiRestController::add_' => array(
    array('#name' => 'param', '#type' => 'mindplay\\annotations\\standard\\ParamAnnotation', 'type' => 'string', 'name' => 'resource'),
    array('#name' => 'route', '#type' => 'Ubiquity\\annotations\\router\\RouteAnnotation', "{resource}/","methods"=>["post"],"priority"=>0),
    array('#name' => 'authorization', '#type' => 'Ubiquity\\annotations\\rest\\AuthorizationAnnotation')
  ),
  'Ubiquity\\controllers\\rest\\api\\jsonapi\\JsonApiRestController::update_' => array(
    array('#name' => 'param', '#type' => 'mindplay\\annotations\\standard\\ParamAnnotation', 'type' => 'string', 'name' => 'resource'),
    array('#name' => 'route', '#type' => 'Ubiquity\\annotations\\router\\RouteAnnotation', "{resource}/{id}","methods"=>["patch"],"priority"=>0),
    array('#name' => 'authorization', '#type' => 'Ubiquity\\annotations\\rest\\AuthorizationAnnotation')
  ),
  'Ubiquity\\controllers\\rest\\api\\jsonapi\\JsonApiRestController::delete_' => array(
    array('#name' => 'param', '#type' => 'mindplay\\annotations\\standard\\ParamAnnotation', 'type' => 'string', 'name' => 'resource'),
    array('#name' => 'param', '#type' => 'mindplay\\annotations\\standard\\ParamAnnotation', 'type' => 'string', 'name' => 'ids'),
    array('#name' => 'route', '#type' => 'Ubiquity\\annotations\\router\\RouteAnnotation', "{resource}/{id}/","methods"=>["delete"],"priority"=>0),
    array('#name' => 'authorization', '#type' => 'Ubiquity\\annotations\\rest\\AuthorizationAnnotation')
  ),
  'Ubiquity\\controllers\\rest\\api\\jsonapi\\JsonApiRestController::_getApiVersion' => array(
    array('#name' => 'return', '#type' => 'mindplay\\annotations\\standard\\ReturnAnnotation', 'type' => 'string')
  ),
  'Ubiquity\\controllers\\rest\\api\\jsonapi\\JsonApiRestController::_getTemplateFile' => array(
    array('#name' => 'return', '#type' => 'mindplay\\annotations\\standard\\ReturnAnnotation', 'type' => 'string')
  ),
);


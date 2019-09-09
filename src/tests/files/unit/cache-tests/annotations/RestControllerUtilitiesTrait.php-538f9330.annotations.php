<?php

return array(
  '#namespace' => 'Ubiquity\\controllers\\rest',
  '#uses' => array (
  'DAO' => 'Ubiquity\\orm\\DAO',
  'UString' => 'Ubiquity\\utils\\base\\UString',
  'URequest' => 'Ubiquity\\utils\\http\\URequest',
  'ValidatorsManager' => 'Ubiquity\\contents\\validation\\ValidatorsManager',
  'ConstraintViolation' => 'Ubiquity\\contents\\validation\\validators\\ConstraintViolation',
  'OrmUtils' => 'Ubiquity\\orm\\OrmUtils',
  'Reflexion' => 'Ubiquity\\orm\\parser\\Reflexion',
),
  '#traitMethodOverrides' => array (
  'Ubiquity\\controllers\\rest\\RestControllerUtilitiesTrait' => 
  array (
  ),
),
  'Ubiquity\\controllers\\rest\\RestControllerUtilitiesTrait' => array(
    array('#name' => 'property', '#type' => 'mindplay\\annotations\\standard\\PropertyAnnotation', 'type' => 'ResponseFormatter', 'name' => 'responseFormatter'),
    array('#name' => 'property', '#type' => 'mindplay\\annotations\\standard\\PropertyAnnotation', 'type' => 'RestServer', 'name' => 'server'),
    array('#name' => 'property', '#type' => 'mindplay\\annotations\\standard\\PropertyAnnotation', 'type' => 'string', 'name' => 'model')
  ),
  'Ubiquity\\controllers\\rest\\RestControllerUtilitiesTrait::getRequestParam' => array(
    array('#name' => 'param', '#type' => 'mindplay\\annotations\\standard\\ParamAnnotation', 'type' => 'string', 'name' => 'param'),
    array('#name' => 'param', '#type' => 'mindplay\\annotations\\standard\\ParamAnnotation', 'type' => 'string|boolean', 'name' => 'default'),
    array('#name' => 'return', '#type' => 'mindplay\\annotations\\standard\\ReturnAnnotation', 'type' => 'string|boolean')
  ),
  'Ubiquity\\controllers\\rest\\RestControllerUtilitiesTrait::_getResponseFormatter' => array(
    array('#name' => 'return', '#type' => 'mindplay\\annotations\\standard\\ReturnAnnotation', 'type' => '\\Ubiquity\\controllers\\rest\\ResponseFormatter')
  ),
  'Ubiquity\\controllers\\rest\\RestControllerUtilitiesTrait::getResponseFormatter' => array(
    array('#name' => 'return', '#type' => 'mindplay\\annotations\\standard\\ReturnAnnotation', 'type' => '\\Ubiquity\\controllers\\rest\\ResponseFormatter')
  ),
  'Ubiquity\\controllers\\rest\\RestControllerUtilitiesTrait::getRestServer' => array(
    array('#name' => 'return', '#type' => 'mindplay\\annotations\\standard\\ReturnAnnotation', 'type' => '\\Ubiquity\\controllers\\rest\\RestServer')
  ),
  'Ubiquity\\controllers\\rest\\RestControllerUtilitiesTrait::_setValuesToObject' => array(
    array('#name' => 'param', '#type' => 'mindplay\\annotations\\standard\\ParamAnnotation', 'type' => 'object', 'name' => 'instance'),
    array('#name' => 'param', '#type' => 'mindplay\\annotations\\standard\\ParamAnnotation', 'type' => 'array', 'name' => 'values')
  ),
  'Ubiquity\\controllers\\rest\\RestControllerUtilitiesTrait::getInclude' => array(
    array('#name' => 'param', '#type' => 'mindplay\\annotations\\standard\\ParamAnnotation', 'type' => 'string|boolean', 'name' => 'include'),
    array('#name' => 'return', '#type' => 'mindplay\\annotations\\standard\\ReturnAnnotation', 'type' => 'array|boolean')
  ),
  'Ubiquity\\controllers\\rest\\RestControllerUtilitiesTrait::getAssociatedMemberValues_' => array(
    array('#name' => 'param', '#type' => 'mindplay\\annotations\\standard\\ParamAnnotation', 'type' => 'string', 'name' => 'ids'),
    array('#name' => 'param', '#type' => 'mindplay\\annotations\\standard\\ParamAnnotation', 'type' => 'callable', 'name' => 'getDatas'),
    array('#name' => 'param', '#type' => 'mindplay\\annotations\\standard\\ParamAnnotation', 'type' => 'string', 'name' => 'member'),
    array('#name' => 'param', '#type' => 'mindplay\\annotations\\standard\\ParamAnnotation', 'type' => 'boolean|string', 'name' => 'include'),
    array('#name' => 'param', '#type' => 'mindplay\\annotations\\standard\\ParamAnnotation', 'type' => 'boolean', 'name' => 'useCache'),
    array('#name' => 'param', '#type' => 'mindplay\\annotations\\standard\\ParamAnnotation', 'type' => 'boolean', 'name' => 'multiple')
  ),
);


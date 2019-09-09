<?php

return array(
  '#namespace' => 'Ubiquity\\controllers\\admin\\traits',
  '#uses' => array (
  'HtmlMessage' => 'Ajax\\semantic\\html\\collections\\HtmlMessage',
  'MaintenanceMode' => 'Ubiquity\\controllers\\admin\\popo\\MaintenanceMode',
  'CacheManager' => 'Ubiquity\\cache\\CacheManager',
  'DefaultMaintenance' => 'Ubiquity\\controllers\\admin\\DefaultMaintenance',
  'Startup' => 'Ubiquity\\controllers\\Startup',
  'ClassUtils' => 'Ubiquity\\cache\\ClassUtils',
  'ControllerAction' => 'Ubiquity\\controllers\\admin\\popo\\ControllerAction',
  'URequest' => 'Ubiquity\\utils\\http\\URequest',
  'Rule' => 'Ajax\\semantic\\components\\validation\\Rule',
  'CheckboxType' => 'Ajax\\semantic\\html\\base\\constants\\CheckboxType',
),
  '#traitMethodOverrides' => array (
  'Ubiquity\\controllers\\admin\\traits\\MaintenanceTrait' => 
  array (
  ),
),
  'Ubiquity\\controllers\\admin\\traits\\MaintenanceTrait' => array(
    array('#name' => 'property', '#type' => 'mindplay\\annotations\\standard\\PropertyAnnotation', 'type' => '\\Ajax\\php\\ubiquity\\JsUtils', 'name' => 'jquery'),
    array('#name' => 'property', '#type' => 'mindplay\\annotations\\standard\\PropertyAnnotation', 'type' => '\\Ubiquity\\views\\View', 'name' => 'view'),
    array('#name' => 'property', '#type' => 'mindplay\\annotations\\standard\\PropertyAnnotation', 'type' => 'array', 'name' => 'config'),
    array('#name' => 'property', '#type' => 'mindplay\\annotations\\standard\\PropertyAnnotation', 'type' => '\\Ubiquity\\scaffolding\\AdminScaffoldController', 'name' => 'scaffold')
  ),
);


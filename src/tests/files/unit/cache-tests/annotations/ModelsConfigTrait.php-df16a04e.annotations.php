<?php

return array(
  '#namespace' => 'Ubiquity\\controllers\\admin\\traits',
  '#uses' => array (
  'URequest' => 'Ubiquity\\utils\\http\\URequest',
  'HtmlMenu' => 'Ajax\\semantic\\html\\collections\\menus\\HtmlMenu',
  'HtmlDropdown' => 'Ajax\\semantic\\html\\modules\\HtmlDropdown',
  'YumlModelsCreator' => 'Ubiquity\\orm\\creator\\yuml\\YumlModelsCreator',
  'Startup' => 'Ubiquity\\controllers\\Startup',
  'UbiquityMyAdminFiles' => 'Ubiquity\\controllers\\admin\\UbiquityMyAdminFiles',
  'Rule' => 'Ajax\\semantic\\components\\validation\\Rule',
  'DAO' => 'Ubiquity\\orm\\DAO',
  'JsUtils' => 'Ajax\\JsUtils',
  'Database' => 'Ubiquity\\db\\Database',
),
  '#traitMethodOverrides' => array (
  'Ubiquity\\controllers\\admin\\traits\\ModelsConfigTrait' => 
  array (
  ),
),
  'Ubiquity\\controllers\\admin\\traits\\ModelsConfigTrait' => array(
    array('#name' => 'property', '#type' => 'mindplay\\annotations\\standard\\PropertyAnnotation', 'type' => '\\Ajax\\php\\ubiquity\\JsUtils', 'name' => 'jquery'),
    array('#name' => 'property', '#type' => 'mindplay\\annotations\\standard\\PropertyAnnotation', 'type' => 'View', 'name' => 'view')
  ),
  'Ubiquity\\controllers\\admin\\traits\\ModelsConfigTrait::_getFiles' => array(
    array('#name' => 'return', '#type' => 'mindplay\\annotations\\standard\\ReturnAnnotation', 'type' => 'UbiquityMyAdminFiles')
  ),
);


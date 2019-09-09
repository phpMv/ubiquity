<?php

return array(
  '#namespace' => 'Ubiquity\\controllers\\admin\\traits',
  '#uses' => array (
  'Startup' => 'Ubiquity\\controllers\\Startup',
  'DAO' => 'Ubiquity\\orm\\DAO',
  'UString' => 'Ubiquity\\utils\\base\\UString',
  'CacheManager' => 'Ubiquity\\cache\\CacheManager',
  'InfoMessage' => 'Ubiquity\\controllers\\admin\\popo\\InfoMessage',
  'Database' => 'Ubiquity\\db\\Database',
  'HtmlSemDoubleElement' => 'Ajax\\semantic\\html\\base\\HtmlSemDoubleElement',
  'UFileSystem' => 'Ubiquity\\utils\\base\\UFileSystem',
  'ArrayCache' => 'Ubiquity\\cache\\system\\ArrayCache',
  'DbModelsCreator' => 'Ubiquity\\orm\\creator\\database\\DbModelsCreator',
  'UbiquityMyAdminFiles' => 'Ubiquity\\controllers\\admin\\UbiquityMyAdminFiles',
  'HtmlMessage' => 'Ajax\\semantic\\html\\collections\\HtmlMessage',
  'UArray' => 'Ubiquity\\utils\\base\\UArray',
  'HtmlDropdown' => 'Ajax\\semantic\\html\\modules\\HtmlDropdown',
  'Reflexion' => 'Ubiquity\\orm\\parser\\Reflexion',
),
  '#traitMethodOverrides' => array (
  'Ubiquity\\controllers\\admin\\traits\\CheckTrait' => 
  array (
  ),
),
  'Ubiquity\\controllers\\admin\\traits\\CheckTrait' => array(
    array('#name' => 'property', '#type' => 'mindplay\\annotations\\standard\\PropertyAnnotation', 'type' => 'array', 'name' => 'steps'),
    array('#name' => 'property', '#type' => 'mindplay\\annotations\\standard\\PropertyAnnotation', 'type' => 'int', 'name' => 'activeStep'),
    array('#name' => 'property', '#type' => 'mindplay\\annotations\\standard\\PropertyAnnotation', 'type' => 'string', 'name' => 'engineering'),
    array('#name' => 'property', '#type' => 'mindplay\\annotations\\standard\\PropertyAnnotation', 'type' => '\\Ajax\\JsUtils', 'name' => 'jquery')
  ),
  'Ubiquity\\controllers\\admin\\traits\\CheckTrait::_getFiles' => array(
    array('#name' => 'return', '#type' => 'mindplay\\annotations\\standard\\ReturnAnnotation', 'type' => 'UbiquityMyAdminFiles')
  ),
);


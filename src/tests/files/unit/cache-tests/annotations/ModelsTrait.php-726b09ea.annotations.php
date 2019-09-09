<?php

return array(
  '#namespace' => 'Ubiquity\\controllers\\admin\\traits',
  '#uses' => array (
  'OrmUtils' => 'Ubiquity\\orm\\OrmUtils',
  'DAO' => 'Ubiquity\\orm\\DAO',
  'JString' => 'Ajax\\service\\JString',
  'Startup' => 'Ubiquity\\controllers\\Startup',
  'HtmlCheckbox' => 'Ajax\\semantic\\html\\modules\\checkbox\\HtmlCheckbox',
  'HtmlMessage' => 'Ajax\\semantic\\html\\collections\\HtmlMessage',
  'CRUDHelper' => 'Ubiquity\\controllers\\crud\\CRUDHelper',
  'CRUDMessage' => 'Ubiquity\\controllers\\crud\\CRUDMessage',
  'URequest' => 'Ubiquity\\utils\\http\\URequest',
  'UResponse' => 'Ubiquity\\utils\\http\\UResponse',
  'ResponseFormatter' => 'Ubiquity\\controllers\\rest\\ResponseFormatter',
  'Pagination' => 'Ajax\\semantic\\widgets\\datatable\\Pagination',
  'UString' => 'Ubiquity\\utils\\base\\UString',
  'HtmlContentOnly' => 'Ajax\\common\\html\\HtmlContentOnly',
  'ValidatorsManager' => 'Ubiquity\\contents\\validation\\ValidatorsManager',
  'TransformersManager' => 'Ubiquity\\contents\\transformation\\TransformersManager',
  'CacheManager' => 'Ubiquity\\cache\\CacheManager',
  'ClassUtils' => 'Ubiquity\\cache\\ClassUtils',
),
  '#traitMethodOverrides' => array (
  'Ubiquity\\controllers\\admin\\traits\\ModelsTrait' => 
  array (
  ),
),
  'Ubiquity\\controllers\\admin\\traits\\ModelsTrait' => array(
    array('#name' => 'property', '#type' => 'mindplay\\annotations\\standard\\PropertyAnnotation', 'type' => '\\Ajax\\JsUtils', 'name' => 'jquery')
  ),
  'Ubiquity\\controllers\\admin\\traits\\ModelsTrait::_getModelViewer' => array(
    array('#name' => 'return', '#type' => 'mindplay\\annotations\\standard\\ReturnAnnotation', 'type' => '\\Ubiquity\\controllers\\crud\\viewers\\ModelViewer')
  ),
);


<?php

return array(
  '#namespace' => 'Ubiquity\\controllers\\admin',
  '#uses' => array (
  'ControllerBase' => 'Ubiquity\\controllers\\ControllerBase',
  'MaintenanceMode' => 'Ubiquity\\controllers\\admin\\popo\\MaintenanceMode',
  'HtmlDimmer' => 'Ajax\\semantic\\html\\modules\\HtmlDimmer',
  'HtmlForm' => 'Ajax\\semantic\\html\\collections\\form\\HtmlForm',
  'URequest' => 'Ubiquity\\utils\\http\\URequest',
  'Startup' => 'Ubiquity\\controllers\\Startup',
  'HtmlMessage' => 'Ajax\\semantic\\html\\collections\\HtmlMessage',
  'UArray' => 'Ubiquity\\utils\\base\\UArray',
  'UFileSystem' => 'Ubiquity\\utils\\base\\UFileSystem',
  'HtmlSegment' => 'Ajax\\semantic\\html\\elements\\HtmlSegment',
  'HtmlLabel' => 'Ajax\\semantic\\html\\elements\\HtmlLabel',
  'InsertJqueryTrait' => 'Ubiquity\\controllers\\semantic\\InsertJqueryTrait',
),
  '#traitMethodOverrides' => array (
  'Ubiquity\\controllers\\admin\\DefaultMaintenance' => 
  array (
  ),
),
  'Ubiquity\\controllers\\admin\\DefaultMaintenance' => array(
    array('#name' => 'property', '#type' => 'mindplay\\annotations\\standard\\PropertyAnnotation', 'type' => '\\Ajax\\php\\ubiquity\\JsUtils', 'name' => 'jquery')
  ),
  'Ubiquity\\controllers\\admin\\DefaultMaintenance::$dimmer' => array(
    array('#name' => 'var', '#type' => 'mindplay\\annotations\\standard\\VarAnnotation', 'type' => 'HtmlDimmer')
  ),
);


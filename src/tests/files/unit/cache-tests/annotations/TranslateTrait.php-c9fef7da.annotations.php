<?php

return array(
  '#namespace' => 'Ubiquity\\controllers\\admin\\traits',
  '#uses' => array (
  'Rule' => 'Ajax\\semantic\\components\\validation\\Rule',
  'Direction' => 'Ajax\\semantic\\html\\base\\constants\\Direction',
  'HtmlMessage' => 'Ajax\\semantic\\html\\collections\\HtmlMessage',
  'HtmlFormInput' => 'Ajax\\semantic\\html\\collections\\form\\HtmlFormInput',
  'HtmlFormTextarea' => 'Ajax\\semantic\\html\\collections\\form\\HtmlFormTextarea',
  'HtmlLabel' => 'Ajax\\semantic\\html\\elements\\HtmlLabel',
  'HtmlList' => 'Ajax\\semantic\\html\\elements\\HtmlList',
  'PositionInTable' => 'Ajax\\semantic\\widgets\\datatable\\PositionInTable',
  'JString' => 'Ajax\\service\\JString',
  'CacheFile' => 'Ubiquity\\cache\\CacheFile',
  'CacheManager' => 'Ubiquity\\cache\\CacheManager',
  'TranslateMessage' => 'Ubiquity\\controllers\\admin\\popo\\TranslateMessage',
  'MessagesCatalog' => 'Ubiquity\\translation\\MessagesCatalog',
  'MessagesDomain' => 'Ubiquity\\translation\\MessagesDomain',
  'MessagesUpdates' => 'Ubiquity\\translation\\MessagesUpdates',
  'TranslatorManager' => 'Ubiquity\\translation\\TranslatorManager',
  'UArray' => 'Ubiquity\\utils\\base\\UArray',
  'URequest' => 'Ubiquity\\utils\\http\\URequest',
  'USession' => 'Ubiquity\\utils\\http\\USession',
),
  '#traitMethodOverrides' => array (
  'Ubiquity\\controllers\\admin\\traits\\TranslateTrait' => 
  array (
  ),
),
  'Ubiquity\\controllers\\admin\\traits\\TranslateTrait' => array(
    array('#name' => 'property', '#type' => 'mindplay\\annotations\\standard\\PropertyAnnotation', 'type' => '\\Ajax\\php\\ubiquity\\JsUtils', 'name' => 'jquery')
  ),
  'Ubiquity\\controllers\\admin\\traits\\TranslateTrait::getDtDomain' => array(
    array('#name' => 'param', '#type' => 'mindplay\\annotations\\standard\\ParamAnnotation', 'type' => 'array', 'name' => 'messages'),
    array('#name' => 'param', '#type' => 'mindplay\\annotations\\standard\\ParamAnnotation', 'type' => 'string', 'name' => 'locale'),
    array('#name' => 'param', '#type' => 'mindplay\\annotations\\standard\\ParamAnnotation', 'type' => 'string', 'name' => 'domain'),
    array('#name' => 'param', '#type' => 'mindplay\\annotations\\standard\\ParamAnnotation', 'type' => 'string', 'name' => 'localeCompare'),
    array('#name' => 'return', '#type' => 'mindplay\\annotations\\standard\\ReturnAnnotation', 'type' => '\\Ajax\\semantic\\widgets\\datatable\\DataTable')
  ),
);


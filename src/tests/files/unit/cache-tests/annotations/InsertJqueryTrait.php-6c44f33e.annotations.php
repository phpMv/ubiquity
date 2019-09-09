<?php

return array(
  '#namespace' => 'Ubiquity\\controllers\\semantic',
  '#uses' => array (
  'JsUtils' => 'Ajax\\JsUtils',
),
  '#traitMethodOverrides' => array (
  'Ubiquity\\controllers\\semantic\\InsertJqueryTrait' => 
  array (
  ),
),
  'Ubiquity\\controllers\\semantic\\InsertJqueryTrait::$jquery' => array(
    array('#name' => 'var', '#type' => 'mindplay\\annotations\\standard\\VarAnnotation', 'type' => 'JsUtils')
  ),
);


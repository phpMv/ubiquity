<?php

return array(
  '#namespace' => 'controllers',
  '#uses' => array (
  'URequest' => 'Ubiquity\\utils\\http\\URequest',
),
  '#traitMethodOverrides' => array (
  'controllers\\RestTester' => 
  array (
  ),
),
  'controllers\\RestTester' => array(
    array('#name' => 'property', '#type' => 'mindplay\\annotations\\standard\\PropertyAnnotation', 'type' => '\\Ajax\\JsUtils', 'name' => 'jquery')
  ),
);


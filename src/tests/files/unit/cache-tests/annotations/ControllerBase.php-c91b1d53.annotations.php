<?php

return array(
  '#namespace' => 'controllers',
  '#uses' => array (
  'Controller' => 'Ubiquity\\controllers\\Controller',
  'URequest' => 'Ubiquity\\utils\\http\\URequest',
),
  '#traitMethodOverrides' => array (
  'controllers\\ControllerBase' => 
  array (
  ),
),
);


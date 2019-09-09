<?php

return array(
  '#namespace' => 'Ubiquity\\annotations\\router',
  '#uses' => array (
  'BaseAnnotation' => 'Ubiquity\\annotations\\BaseAnnotation',
),
  '#traitMethodOverrides' => array (
  'Ubiquity\\annotations\\router\\RouteAnnotation' => 
  array (
  ),
),
  'Ubiquity\\annotations\\router\\RouteAnnotation' => array(
    array('#name' => 'usage', '#type' => 'mindplay\\annotations\\UsageAnnotation', 'method'=>true,'class'=>true,'multiple'=>true, 'inherited'=>true)
  ),
);


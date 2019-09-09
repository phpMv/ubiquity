<?php

return array(
  '#namespace' => 'Ubiquity\\annotations\\rest',
  '#uses' => array (
  'BaseAnnotation' => 'Ubiquity\\annotations\\BaseAnnotation',
),
  '#traitMethodOverrides' => array (
  'Ubiquity\\annotations\\rest\\AuthorizationAnnotation' => 
  array (
  ),
),
  'Ubiquity\\annotations\\rest\\AuthorizationAnnotation' => array(
    array('#name' => 'usage', '#type' => 'mindplay\\annotations\\UsageAnnotation', 'method'=>true, 'inherited'=>true)
  ),
);


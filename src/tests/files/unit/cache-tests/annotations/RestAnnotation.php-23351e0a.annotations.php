<?php

return array(
  '#namespace' => 'Ubiquity\\annotations\\rest',
  '#uses' => array (
  'BaseAnnotation' => 'Ubiquity\\annotations\\BaseAnnotation',
),
  '#traitMethodOverrides' => array (
  'Ubiquity\\annotations\\rest\\RestAnnotation' => 
  array (
  ),
),
  'Ubiquity\\annotations\\rest\\RestAnnotation' => array(
    array('#name' => 'usage', '#type' => 'mindplay\\annotations\\UsageAnnotation', 'class'=>true, 'inherited'=>true)
  ),
);


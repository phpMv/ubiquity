<?php

return array(
  '#namespace' => 'Ubiquity\\annotations',
  '#uses' => array (
  'UArray' => 'Ubiquity\\utils\\base\\UArray',
  'Annotation' => 'mindplay\\annotations\\Annotation',
),
  '#traitMethodOverrides' => array (
  'Ubiquity\\annotations\\BaseAnnotation' => 
  array (
  ),
),
  'Ubiquity\\annotations\\BaseAnnotation' => array(
    array('#name' => 'usage', '#type' => 'mindplay\\annotations\\UsageAnnotation', 'property'=>true, 'inherited'=>true)
  ),
);


<?php

return array(
  '#namespace' => 'Ubiquity\\annotations',
  '#uses' => array (
  'ValidatorsManager' => 'Ubiquity\\contents\\validation\\ValidatorsManager',
  'UArray' => 'Ubiquity\\utils\\base\\UArray',
),
  '#traitMethodOverrides' => array (
  'Ubiquity\\annotations\\ValidatorAnnotation' => 
  array (
  ),
),
  'Ubiquity\\annotations\\ValidatorAnnotation' => array(
    array('#name' => 'usage', '#type' => 'mindplay\\annotations\\UsageAnnotation', 'property'=>true, 'inherited'=>true, 'multiple'=>true)
  ),
);


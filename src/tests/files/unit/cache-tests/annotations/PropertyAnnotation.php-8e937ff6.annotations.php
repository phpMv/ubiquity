<?php

return array(
  '#namespace' => 'mindplay\\annotations\\standard',
  '#uses' => array (
  'Annotation' => 'mindplay\\annotations\\Annotation',
  'AnnotationException' => 'mindplay\\annotations\\AnnotationException',
  'AnnotationFile' => 'mindplay\\annotations\\AnnotationFile',
  'IAnnotationFileAware' => 'mindplay\\annotations\\IAnnotationFileAware',
  'IAnnotationParser' => 'mindplay\\annotations\\IAnnotationParser',
),
  '#traitMethodOverrides' => array (
  'mindplay\\annotations\\standard\\PropertyAnnotation' => 
  array (
  ),
),
  'mindplay\\annotations\\standard\\PropertyAnnotation' => array(
    array('#name' => 'usage', '#type' => 'mindplay\\annotations\\UsageAnnotation', 'class'=>true, 'inherited'=>true)
  ),
  'mindplay\\annotations\\standard\\PropertyAnnotation::$type' => array(
    array('#name' => 'var', '#type' => 'mindplay\\annotations\\standard\\VarAnnotation', 'type' => 'string')
  ),
  'mindplay\\annotations\\standard\\PropertyAnnotation::$name' => array(
    array('#name' => 'var', '#type' => 'mindplay\\annotations\\standard\\VarAnnotation', 'type' => 'string')
  ),
  'mindplay\\annotations\\standard\\PropertyAnnotation::$description' => array(
    array('#name' => 'var', '#type' => 'mindplay\\annotations\\standard\\VarAnnotation', 'type' => 'string')
  ),
  'mindplay\\annotations\\standard\\PropertyAnnotation::$file' => array(
    array('#name' => 'var', '#type' => 'mindplay\\annotations\\standard\\VarAnnotation', 'type' => 'AnnotationFile')
  ),
  'mindplay\\annotations\\standard\\PropertyAnnotation::parseAnnotation' => array(
    array('#name' => 'param', '#type' => 'mindplay\\annotations\\standard\\ParamAnnotation', 'type' => 'string', 'name' => 'value'),
    array('#name' => 'return', '#type' => 'mindplay\\annotations\\standard\\ReturnAnnotation', 'type' => 'array')
  ),
  'mindplay\\annotations\\standard\\PropertyAnnotation::setAnnotationFile' => array(
    array('#name' => 'param', '#type' => 'mindplay\\annotations\\standard\\ParamAnnotation', 'type' => 'AnnotationFile', 'name' => 'file'),
    array('#name' => 'return', '#type' => 'mindplay\\annotations\\standard\\ReturnAnnotation', 'type' => 'void')
  ),
);


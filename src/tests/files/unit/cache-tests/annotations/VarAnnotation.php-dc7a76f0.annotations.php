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
  'mindplay\\annotations\\standard\\VarAnnotation' => 
  array (
  ),
),
  'mindplay\\annotations\\standard\\VarAnnotation' => array(
    array('#name' => 'usage', '#type' => 'mindplay\\annotations\\UsageAnnotation', 'property'=>true, 'inherited'=>true)
  ),
  'mindplay\\annotations\\standard\\VarAnnotation::$type' => array(
    array('#name' => 'var', '#type' => 'mindplay\\annotations\\standard\\VarAnnotation', 'type' => 'string')
  ),
  'mindplay\\annotations\\standard\\VarAnnotation::$file' => array(
    array('#name' => 'var', '#type' => 'mindplay\\annotations\\standard\\VarAnnotation', 'type' => 'AnnotationFile')
  ),
  'mindplay\\annotations\\standard\\VarAnnotation::parseAnnotation' => array(
    array('#name' => 'param', '#type' => 'mindplay\\annotations\\standard\\ParamAnnotation', 'type' => 'string', 'name' => 'value'),
    array('#name' => 'return', '#type' => 'mindplay\\annotations\\standard\\ReturnAnnotation', 'type' => 'array')
  ),
  'mindplay\\annotations\\standard\\VarAnnotation::setAnnotationFile' => array(
    array('#name' => 'param', '#type' => 'mindplay\\annotations\\standard\\ParamAnnotation', 'type' => 'AnnotationFile', 'name' => 'file'),
    array('#name' => 'return', '#type' => 'mindplay\\annotations\\standard\\ReturnAnnotation', 'type' => 'void')
  ),
);


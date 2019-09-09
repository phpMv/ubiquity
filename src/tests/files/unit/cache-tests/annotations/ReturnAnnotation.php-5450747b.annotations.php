<?php

return array(
  '#namespace' => 'mindplay\\annotations\\standard',
  '#uses' => array (
  'AnnotationException' => 'mindplay\\annotations\\AnnotationException',
  'AnnotationFile' => 'mindplay\\annotations\\AnnotationFile',
  'IAnnotationFileAware' => 'mindplay\\annotations\\IAnnotationFileAware',
  'IAnnotationParser' => 'mindplay\\annotations\\IAnnotationParser',
  'Annotation' => 'mindplay\\annotations\\Annotation',
),
  '#traitMethodOverrides' => array (
  'mindplay\\annotations\\standard\\ReturnAnnotation' => 
  array (
  ),
),
  'mindplay\\annotations\\standard\\ReturnAnnotation' => array(
    array('#name' => 'usage', '#type' => 'mindplay\\annotations\\UsageAnnotation', 'method'=>true, 'inherited'=>true)
  ),
  'mindplay\\annotations\\standard\\ReturnAnnotation::$type' => array(
    array('#name' => 'var', '#type' => 'mindplay\\annotations\\standard\\VarAnnotation', 'type' => 'string')
  ),
  'mindplay\\annotations\\standard\\ReturnAnnotation::$file' => array(
    array('#name' => 'var', '#type' => 'mindplay\\annotations\\standard\\VarAnnotation', 'type' => 'AnnotationFile')
  ),
  'mindplay\\annotations\\standard\\ReturnAnnotation::parseAnnotation' => array(
    array('#name' => 'param', '#type' => 'mindplay\\annotations\\standard\\ParamAnnotation', 'type' => 'string', 'name' => 'value'),
    array('#name' => 'return', '#type' => 'mindplay\\annotations\\standard\\ReturnAnnotation', 'type' => 'array')
  ),
  'mindplay\\annotations\\standard\\ReturnAnnotation::setAnnotationFile' => array(
    array('#name' => 'param', '#type' => 'mindplay\\annotations\\standard\\ParamAnnotation', 'type' => 'AnnotationFile', 'name' => 'file'),
    array('#name' => 'return', '#type' => 'mindplay\\annotations\\standard\\ReturnAnnotation', 'type' => 'void')
  ),
);


<?php

return array(
  '#namespace' => 'controllers',
  '#uses' => array (
  'JsonApiResponseFormatter' => 'Ubiquity\\controllers\\rest\\api\\jsonapi\\JsonApiResponseFormatter',
  'ResponseFormatter' => 'Ubiquity\\controllers\\rest\\ResponseFormatter',
  'JsonApiRestController' => 'Ubiquity\\controllers\\rest\\api\\jsonapi\\JsonApiRestController',
),
  '#traitMethodOverrides' => array (
  'controllers\\TestRestJsonApi' => 
  array (
  ),
),
  'controllers\\TestRestJsonApi' => array(
    array('#name' => 'route', '#type' => 'Ubiquity\\annotations\\router\\RouteAnnotation', "/jsonapi/","inherited"=>true,"automated"=>true),
    array('#name' => 'rest', '#type' => 'Ubiquity\\annotations\\rest\\RestAnnotation')
  ),
);


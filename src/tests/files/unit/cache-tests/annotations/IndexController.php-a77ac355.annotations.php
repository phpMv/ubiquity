<?php

return array(
  '#namespace' => 'controllers',
  '#uses' => array (
  'Display' => 'Ubiquity\\core\\postinstall\\Display',
  'Logger' => 'Ubiquity\\log\\Logger',
  'ThemesManager' => 'Ubiquity\\themes\\ThemesManager',
  'DAO' => 'Ubiquity\\orm\\DAO',
  'User' => 'models\\User',
  'Organization' => 'models\\Organization',
  'Startup' => 'Ubiquity\\controllers\\Startup',
  'ClassesToYuml' => 'Ubiquity\\utils\\yuml\\ClassesToYuml',
),
  '#traitMethodOverrides' => array (
  'controllers\\IndexController' => 
  array (
  ),
),
);


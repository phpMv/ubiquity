<?php
return array("id"=>array(array("type"=>"id","constraints"=>array("autoinc"=>true))),"dateCo"=>array(array("type"=>"type","constraints"=>array("ref"=>"dateTime","notNull"=>true))),"url"=>array(array("type"=>"url","constraints"=>array("notNull"=>true)),array("type"=>"length","constraints"=>array("max"=>255))));

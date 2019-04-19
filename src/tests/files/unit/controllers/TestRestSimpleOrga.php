<?php
namespace controllers;

/**
 * Rest Controller TestRestSimpleOrga
 * @route("/rest/simple/orgas/","inherited"=>true,"automated"=>true)
 * @rest("resource"=>"models\\Organization")
 */
class TestRestSimpleOrga extends \Ubiquity\controllers\rest\SimpleRestController {
	
	public function isValid($action){
		return true;
	}
}

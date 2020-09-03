<?php

namespace controllers;

use Ubiquity\controllers\rest\RestController;

/**
 *
 * @route("/rest/simple/user","inherited"=>true,"automated"=>true)
 * @rest("resource"=>"models\\User")
 */
class TestRestControllerUser extends RestController {

	/**
	 *
	 * @get("")
	 */
	public function index() {
		return parent::_index ();
	}
}


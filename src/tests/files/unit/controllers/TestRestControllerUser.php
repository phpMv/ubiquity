<?php

namespace controllers;

use Ubiquity\controllers\rest\RestResourceController;

/**
 *
 * @route("/rest/simple/user","inherited"=>true,"automated"=>true)
 * @rest("resource"=>"models\\User")
 */
class TestRestControllerUser extends RestResourceController {

	/**
	 *
	 * @get("")
	 */
	public function index() {
		return parent::_get ();
	}
}


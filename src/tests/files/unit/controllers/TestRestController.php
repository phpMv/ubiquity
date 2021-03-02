<?php

namespace controllers;

use Ubiquity\controllers\rest\RestBaseController;

/**
 *
 * @route("/rest/test","inherited"=>false,"automated"=>false)
 * @rest("resource"=>"")
 */
class TestRestController extends RestBaseController {

	public function initialize() {
	}

	/**
	 * Returns all objects for the resource $model
	 *
	 * @route("cache"=>false)
	 */
	public function index() {
		echo $this->_getResponseFormatter ()->toJson ( [ "test" => "ok" ] );
	}

	public function withTicket() {
		echo "autorized!";
	}
}


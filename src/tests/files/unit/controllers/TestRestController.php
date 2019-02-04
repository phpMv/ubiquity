<?php

namespace controllers;

use Ubiquity\controllers\rest\RestController;

/**
 *
 * @route("/rest/test","inherited"=>false,"automated"=>false)
 * @rest("resource"=>"")
 */
class TestRestController extends RestController {

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


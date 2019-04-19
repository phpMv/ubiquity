<?php

namespace controllers;

use Ubiquity\controllers\rest\api\jsonapi\JsonApiResponseFormatter;
use Ubiquity\controllers\rest\ResponseFormatter;
use Ubiquity\controllers\rest\api\jsonapi\JsonApiRestController;

/**
 * Rest Controller TestRestJsonApi
 *
 * @route("/jsonapi/","inherited"=>true,"automated"=>true)
 * @rest
 */
class TestRestJsonApi extends JsonApiRestController {

	protected function getResponseFormatter(): ResponseFormatter {
		return new JsonApiResponseFormatter ( '/jsonapi' );
	}

	public function isValid($action) {
		return true;
	}
}

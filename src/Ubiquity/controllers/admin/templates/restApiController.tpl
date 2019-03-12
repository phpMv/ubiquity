<?php
%namespace%

use Ubiquity\controllers\rest\api\jsonapi\JsonApiResponseFormatter;
use Ubiquity\controllers\rest\ResponseFormatter;
use Ubiquity\controllers\rest\api\jsonapi\JsonApiRestController;

/**
 * Rest API Controller %controllerName%%route%
 * @rest
 */
class %controllerName% extends JsonApiRestController {
	protected function getResponseFormatter(): ResponseFormatter {
		return new JsonApiResponseFormatter('%routeName%');
	}
}

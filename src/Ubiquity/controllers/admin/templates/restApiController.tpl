<?php
%namespace%

use Ubiquity\controllers\rest\api\jsonapi\JsonApiResponseFormatter;
use Ubiquity\controllers\rest\ResponseFormatter;

/**
 * Rest API Controller %controllerName%%route%
 * @rest
 */
class %controllerName% extends %baseClass% {
	protected function getResponseFormatter(): ResponseFormatter {
		return new JsonApiResponseFormatter('%routeName%');
	}
}

<?php

/**
 * JsonApi implementation
 */
namespace Ubiquity\controllers\rest\api\json;

use Ubiquity\controllers\rest\formatters\JsonRequestFormatter;
use Ubiquity\controllers\rest\formatters\RequestFormatter;
use Ubiquity\controllers\rest\formatters\ResponseFormatter;
use Ubiquity\controllers\rest\RestBaseController;
use Ubiquity\controllers\rest\traits\DynamicResourceTrait;

/**
 * Rest Json implementation.
 * Ubiquity\controllers\rest\api\jsonapi$JsonRestController
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 0.0.1
 * @since Ubiquity 2.4.2
 *
 */
abstract class JsonRestController extends RestBaseController {
	use DynamicResourceTrait;
	const API_VERSION = 'JSON REST 1.0';

	protected function getResponseFormatter(): ResponseFormatter {
		return new ResponseFormatter();
	}

	protected function getRequestFormatter(): RequestFormatter {
		return new JsonRequestFormatter();
	}

	/**
	 * Returns the api version.
	 *
	 * @return string
	 */
	public static function _getApiVersion() {
		return self::API_VERSION;
	}

	/**
	 * Returns the template for creating this type of controller
	 *
	 * @return string
	 */
	public static function _getTemplateFile() {
		return 'restDynResourceController.tpl';
	}
}


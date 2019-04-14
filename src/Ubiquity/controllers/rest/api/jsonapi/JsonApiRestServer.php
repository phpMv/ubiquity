<?php

namespace Ubiquity\controllers\rest\api\jsonapi;

use Ubiquity\controllers\rest\RestServer;

/**
 * Rest server for JsonAPI 1.0.
 * Ubiquity\controllers\rest\api\jsonapi$JsonApiRestServer
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.1
 *
 */
class JsonApiRestServer extends RestServer {

	public function __construct(&$config) {
		parent::__construct ( $config );
		$this->headers ['Content-Type'] = 'application/vnd.api+json; charset=utf8';
	}
}


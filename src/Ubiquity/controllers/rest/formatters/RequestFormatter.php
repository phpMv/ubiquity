<?php

namespace Ubiquity\controllers\rest\formatters;

use Ubiquity\utils\http\URequest;

/**
 * Ubiquity\controllers\rest\formatters$RequestFormatter
 * This class is part of Ubiquity
 *
 * @author jc
 * @version 1.0.0
 *
 */
class RequestFormatter {

	public function getDatas(?string $model = null): array {
		return URequest::getDatas ();
	}
}
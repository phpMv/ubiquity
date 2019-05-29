<?php

namespace Ubiquity\utils\http\foundation;

/**
 * Gateway between php and PSR-7 Http messages.
 * Used with react and php-pm servers
 * Ubiquity\utils\http\foundation$Psr7
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.1.0
 *
 */
class Psr7 {

	public static function requestToGlobal(\Psr\Http\Message\ServerRequestInterface $request) {
		$method = $request->getMethod ();
		$parameters = $request->getQueryParams ();
		$parsedBody = $request->getParsedBody ();
		if (strtoupper ( $method ) === 'POST') {
			$_POST = $parsedBody;
		}
		if (sizeof ( $parameters ) > 0) {
			$_GET = array_merge ( $_GET, $parameters );
		}
		$_SERVER = $request->getServerParams ();
		// To ADD $_FILES
	}
}


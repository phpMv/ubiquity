<?php

namespace Ubiquity\utils\http\traits;

use Ubiquity\controllers\Startup;

/**
 * Ubiquity\utils\http\traits$URequestTesterTrait
 * This class is part of Ubiquity
 *
 * @author jc
 * @version 1.0.0
 *
 */
trait URequestTesterTrait {

	/**
	 * Tests if a value is present on the request and is not empty
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public static function filled($key): bool {
		return isset ( $_REQUEST [$key] ) && $_REQUEST [$key] != null;
	}

	/**
	 * Tests if a value is present on the request
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public static function has($key): bool {
		return isset ( $_REQUEST [$key] );
	}

	/**
	 * Returns true if the request is an Ajax request
	 *
	 * @return boolean
	 */
	public static function isAjax(): bool {
		return (isset ( $_SERVER ['HTTP_X_REQUESTED_WITH'] ) && ! empty ( $_SERVER ['HTTP_X_REQUESTED_WITH'] ) && strtolower ( $_SERVER ['HTTP_X_REQUESTED_WITH'] ) == 'xmlhttprequest');
	}

	/**
	 * Returns true if the request is sent by the POST method
	 *
	 * @return boolean
	 */
	public static function isPost(): bool {
		return $_SERVER ['REQUEST_METHOD'] === 'POST';
	}

	/**
	 * Returns true if the request is cross site.
	 * Please note that this method is not secure, and does not protect against CSRF attacks.
	 * For sufficient protection in this area, install the CSRF protection of the ubiquity-security module.
	 *
	 * @see https://micro-framework.readthedocs.io/en/latest/security/module.html#csrf-manager
	 *
	 * @return boolean
	 */
	public static function isCrossSite(): bool {
		return \stripos ( $_SERVER ['HTTP_REFERER'], $_SERVER ['SERVER_NAME'] ) === FALSE;
	}

	/**
	 * Returns true if request contentType is set to json
	 *
	 * @return boolean
	 */
	public static function isJSON(): bool {
		$contentType = self::getContentType ();
		return \stripos ( $contentType, 'json' ) !== false;
	}

	/**
	 * Returns the request content-type header
	 *
	 * @return string
	 */
	public static function getContentType(): ?string {
		$headers = Startup::getHttpInstance ()->getAllHeaders ();
		if (isset ( $headers ['Content-Type'] )) {
			return $headers ['Content-Type'];
		}
		return null;
	}
}


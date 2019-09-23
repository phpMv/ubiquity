<?php

namespace Ubiquity\utils\http;

use Ubiquity\controllers\Startup;

/**
 * Http Response utilities
 * Ubiquity\utils\http$UResponse
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.1.1
 *
 */
class UResponse {
	public static $headers = [ ];

	/**
	 * Send a raw HTTP header
	 *
	 * @param string $headerField the header field
	 * @param string $value the header value
	 * @param boolean $replace The optional replace parameter indicates whether the header should replace a previous similar header
	 * @param int $responseCode Forces the HTTP response code to the specified value
	 */
	public static function header($headerField, $value, bool $replace = true, int $responseCode = null): void {
		self::$headers [trim ( $headerField )] = trim ( $value );
		Startup::getHttpInstance ()->header ( trim ( $headerField ), trim ( $value ), $replace, $responseCode );
	}

	/**
	 *
	 * @param string $headerField
	 * @param mixed $values
	 */
	private static function _headerArray($headerField, $values): void {
		if (\is_array ( $values )) {
			$values = \implode ( ', ', $values );
		}
		self::header ( $headerField, $values );
	}

	/**
	 * Sets header content-type
	 *
	 * @param string $contentType
	 * @param string $encoding
	 */
	public static function setContentType($contentType, $encoding = null): void {
		$value = $contentType;
		if (isset ( $encoding ))
			$value .= '; charset=' . $encoding;
		self::header ( 'Content-Type', $value, true );
	}

	/**
	 * Forces the disabling of the browser cache
	 */
	public static function noCache(): void {
		self::header ( 'Cache-Control', 'no-cache, must-revalidate' );
		self::header ( 'Expires', 'Sat, 26 Jul 1997 05:00:00 GMT' );
	}

	/**
	 * Checks if or where headers have been sent
	 *
	 * @param string $file If the optional file and line parameters are set,headers_sent will put the PHP source file nameand line number where output started in the fileand line variables.
	 * @param int $line The line number where the output started.
	 * @return boolean
	 */
	public static function isSent(&$file = null, &$line = null): bool {
		return Startup::getHttpInstance ()->headersSent ( $file, $line );
	}

	/**
	 * Sets the response content-type to application/json
	 */
	public static function asJSON(): void {
		self::header ( 'Content-Type', 'application/json' );
	}

	/**
	 * Tests if response content-type is application/json
	 * Only Works if UResponse has been used for setting headers
	 *
	 * @return boolean
	 */
	public static function isJSON(): bool {
		return isset ( self::$headers ['Content-Type'] ) && self::$headers ['Content-Type'] === 'application/json';
	}

	/**
	 * Sets the response content-type to text/html
	 *
	 * @param string $encoding default: utf-8
	 */
	public static function asHtml($encoding = 'utf-8'): void {
		self::setContentType ( 'text/html', $encoding );
	}

	/**
	 * Sets the response content-type to application/xml
	 *
	 * @param string $encoding default: utf-8
	 */
	public static function asXml($encoding = 'utf-8'): void {
		self::setContentType ( 'application/xml', $encoding );
	}

	/**
	 * Sets the response content-type to plain/text
	 *
	 * @param string $encoding default: utf-8
	 */
	public static function asText($encoding = 'utf-8'): void {
		self::setContentType ( 'plain/text', $encoding );
	}

	/**
	 * Sets the Accept header
	 *
	 * @param string $value one of Http accept values
	 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Content_negotiation/List_of_default_Accept_values
	 */
	public static function setAccept($value): void {
		self::header ( 'Accept', $value );
	}

	/**
	 * Enables CORS
	 *
	 * @param string $origin The allowed origin (default: '*')
	 * @param string $methods The allowed methods (default: 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
	 * @param string $headers The allowed headers (default: 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
	 * @since Ubiquity 2.0.11
	 */
	public static function enableCors($origin = '*', $methods = 'GET, POST, PUT, DELETE, PATCH, OPTIONS', $headers = 'X-Requested-With, Content-Type, Accept, Origin, Authorization'): void {
		self::setAccessControlOrigin ( $origin );
		self::setAccessControlMethods ( $methods );
		self::setAccessControlHeaders ( $headers );
	}

	/**
	 * Sets the Access-Control-Allow-Origin field value
	 * Only a single origin can be specified.
	 *
	 * @param string $origin
	 */
	public static function setAccessControlOrigin($origin = '*'): void {
		self::header ( 'Access-Control-Allow-Origin', $origin );
		if ($origin !== '*') {
			self::header ( 'Vary', 'Origin' );
		}
	}

	/**
	 * Sets the Access-Control-Allow-Methods field value
	 *
	 * @param string|array $methods
	 */
	public static function setAccessControlMethods($methods): void {
		self::_headerArray ( 'Access-Control-Allow-Methods', $methods );
	}

	/**
	 * Sets the Access-Control-Allow-Headers field value
	 *
	 * @param string|array $headers
	 */
	public static function setAccessControlHeaders($headers): void {
		self::_headerArray ( 'Access-Control-Allow-Headers', $headers );
	}

	/**
	 * Set the Authorization header field
	 *
	 * @param string $authorization
	 */
	public static function setAuthorization($authorization): void {
		self::header ( 'Authorization', $authorization );
	}

	/**
	 * Sets the response code
	 *
	 * @param int $value
	 */
	public static function setResponseCode($value) {
		return \http_response_code ( $value );
	}

	/**
	 * Get the response code
	 *
	 * @return int
	 */
	public static function getResponseCode() {
		return \http_response_code ();
	}
}

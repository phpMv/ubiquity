<?php

namespace Ubiquity\utils\http;

/**
 * Http Response utilities
 * Ubiquity\utils\http$UResponse
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.3
 *
 */
class UResponse {
	public static $headers = [ ];

	/**
	 * Send a raw HTTP header
	 *
	 * @param string $headerField
	 *        	the header field
	 * @param string $value
	 *        	the header value
	 * @param boolean $replace
	 *        	The optional replace parameter indicates whether the header should replace a previous similar header
	 * @param int $responseCode
	 *        	Forces the HTTP response code to the specified value
	 */
	public static function header($headerField, $value, $replace = null, $responseCode = null) {
		self::$headers [trim ( $headerField )] = trim ( $value );
		\header ( trim ( $headerField ) . ": " . trim ( $value ), $replace, $responseCode );
	}

	/**
	 *
	 * @param string $headerField
	 * @param mixed $values
	 */
	private static function _headerArray($headerField, $values) {
		if (\is_array ( $values )) {
			$values = \implode ( ", ", $values );
		}
		self::header ( $headerField, $values );
	}

	/**
	 * Sets header content-type
	 *
	 * @param string $contentType
	 * @param string $encoding
	 */
	public static function setContentType($contentType, $encoding = null) {
		$value = $contentType;
		if (isset ( $encoding ))
			$value .= ' ;charset=' . $encoding;
		self::header ( 'Content-Type', $value );
	}

	/**
	 * Forces the disabling of the browser cache
	 */
	public static function noCache() {
		self::header ( 'Cache-Control', 'no-cache, must-revalidate' );
		self::header ( 'Expires', 'Sat, 26 Jul 1997 05:00:00 GMT' );
	}

	/**
	 * Checks if or where headers have been sent
	 *
	 * @param string $file
	 *        	If the optional file and line parameters are set,headers_sent will put the PHP source file nameand line number where output started in the fileand line variables.
	 * @param int $line
	 *        	The line number where the output started.
	 * @return boolean
	 */
	public static function isSent(&$file = null, &$line = null) {
		return \headers_sent ( $file, $line );
	}

	/**
	 * Sets the response content-type to application/json
	 */
	public static function asJSON() {
		self::header ( 'Content-Type', 'application/json' );
	}

	/**
	 * Tests if response content-type is application/json
	 * Only Works if UResponse has been used for setting headers
	 *
	 * @return boolean
	 */
	public static function isJSON() {
		return isset ( self::$headers ["Content-Type"] ) && self::$headers ["Content-Type"] === 'application/json';
	}

	/**
	 * Sets the response content-type to text/html
	 *
	 * @param string $encoding
	 *        	default: utf-8
	 */
	public static function asHtml($encoding = 'utf-8') {
		self::setContentType ( 'text/html', $encoding );
	}

	/**
	 * Sets the response content-type to application/xml
	 *
	 * @param string $encoding
	 *        	default: utf-8
	 */
	public static function asXml($encoding = 'utf-8') {
		self::setContentType ( 'application/xml', $encoding );
	}

	/**
	 * Sets the response content-type to plain/text
	 *
	 * @param string $encoding
	 *        	default: utf-8
	 */
	public static function asText($encoding = 'utf-8') {
		self::setContentType ( 'plain/text', $encoding );
	}

	/**
	 * Sets the Accept header
	 *
	 * @param string $value
	 *        	one of Http accept values
	 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Content_negotiation/List_of_default_Accept_values
	 */
	public static function setAccept($value) {
		self::header ( 'Accept', $value );
	}

	/**
	 * Enables CORS
	 *
	 * @param string $origin
	 *        	The allowed origin (default: '*')
	 * @param string $methods
	 *        	The allowed methods (default: 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
	 * @param string $headers
	 *        	The allowed headers (default: 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
	 * @since Ubiquity 2.0.11
	 */
	public static function enableCors($origin = '*', $methods = 'GET, POST, PUT, DELETE, PATCH, OPTIONS', $headers = 'X-Requested-With, Content-Type, Accept, Origin, Authorization') {
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
	public static function setAccessControlOrigin($origin = '*') {
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
	public static function setAccessControlMethods($methods) {
		self::_headerArray ( 'Access-Control-Allow-Methods', $methods );
	}

	/**
	 * Sets the Access-Control-Allow-Headers field value
	 *
	 * @param string|array $headers
	 */
	public static function setAccessControlHeaders($headers) {
		self::_headerArray ( 'Access-Control-Allow-Headers', $headers );
	}

	/**
	 * Set the Authorization header field
	 *
	 * @param string $authorization
	 */
	public static function setAuthorization($authorization) {
		self::header ( 'Authorization', $authorization );
	}

	/**
	 * Sets the response code
	 *
	 * @param int $value
	 */
	public static function setResponseCode($value) {
		\http_response_code ( $value );
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

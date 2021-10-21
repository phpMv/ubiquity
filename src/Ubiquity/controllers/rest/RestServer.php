<?php

namespace Ubiquity\controllers\rest;

use Ubiquity\controllers\Startup;
use Ubiquity\cache\ClassUtils;
use Ubiquity\cache\CacheManager;
use Ubiquity\exceptions\RestException;
use Ubiquity\log\Logger;
use Ubiquity\utils\http\URequest;

/**
 * Rest server base class.
 * Ubiquity\controllers\rest$RestServer
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.7
 *
 */
class RestServer {
	/**
	 *
	 * @var array
	 */
	protected $config;
	protected $headers;
	protected $tokensFolder;
	protected $tokenLength;
	protected $tokenDuration;
	protected $tokensCacheKey = "_apiTokens";
	protected $allowedOrigins;

	/**
	 *
	 * @var ApiTokens
	 */
	protected $apiTokens;

	public function __construct(&$config, $headers = null) {
		$this->config = $config;
		$this->headers = [ 'Access-Control-Allow-Origin' => '*','Access-Control-Allow-Credentials' => 'true','Access-Control-Max-Age' => '86400','Access-Control-Allow-Methods' => 'GET, POST, OPTIONS, PUT, DELETE, PATCH, HEAD','Content-Type' => 'application/json; charset=utf8' ];
		if (\is_array ( $headers )) {
			$this->headers = \array_merge ( $this->headers, $headers );
		}
	}

	/**
	 * Establishes the connection with the server, returns an added token in the Authorization header of the request
	 *
	 * @return array
	 */
	public function connect($datas=null) {
		if (! isset ( $this->apiTokens )) {
			$this->apiTokens = $this->_loadApiTokens ();
		}
		$token = $this->apiTokens->addToken ($datas);
		$this->_addHeaderToken ( $token );
		return [ "access_token" => $token,"token_type" => "Bearer","expires_in" => $this->apiTokens->getDuration () ];
	}

	/**
	 * Check if token is valid
	 * @param callable $callback
	 * @return boolean
	 */
	public function isValid($callback) {
		$this->apiTokens = $this->_loadApiTokens ();
		$key = $this->_getHeaderToken ();
		if ($this->apiTokens->isExpired ( $key )) {
			return false;
		} else {
			$token=$this->apiTokens->getToken($key);
			if($callback($token['datas']??null)) {
				$this->_addHeaderToken($key);
				return true;
			}
			return false;
		}
	}

	public function _getHeaderToken() {
		$authHeader = $this->_getHeader ( "Authorization" );
		if ($authHeader !== false) {
			$headerDatas = explode ( " ", $authHeader, 2 );
			if (\count( $headerDatas ) === 2) {
				list ( $type, $data ) = $headerDatas;
				if (\strcasecmp ( $type, "Bearer" ) == 0) {
					return $data;
				} else {
					throw new RestException ( "Bearer is required in authorization header." );
				}
			} else {
				throw new RestException ( "The header Authorization is required in http headers." );
			}
		} else {
			throw new RestException ( "The header Authorization is required in http headers." );
		}
	}

	public function finalizeTokens() {
		if (isset ( $this->apiTokens )) {
			$this->apiTokens->removeExpireds ();
			$this->apiTokens->storeToCache ();
		}
	}

	public function _getHeader($header) {
		$headers = getallheaders ();
		if (isset ( $headers [$header] )) {
			return $headers [$header];
		}
		return false;
	}

	public function _addHeaderToken($token) {
		$this->_header ( "Authorization", "Bearer " . $token, true );
	}

	public function _loadApiTokens() {
		return $this->getApiTokens ()->getFromCache ( CacheManager::getAbsoluteCacheDirectory () . \DS, $this->tokensCacheKey );
	}

	protected function getApiTokens() {
		if (! isset ( $this->apiTokens )) {
			$this->apiTokens = $this->newApiTokens ();
		}
		return $this->apiTokens;
	}

	/**
	 * To override for defining another ApiToken type
	 *
	 * @return ApiTokens
	 */
	protected function newApiTokens() {
		return new ApiTokens ( $this->tokenLength, $this->tokenDuration );
	}

	protected function getAllowedOrigin() {
		$http_origin = URequest::getOrigin ();
		if (is_array ( $this->allowedOrigins )) {
			if (array_search ( $http_origin, $this->allowedOrigins ) !== false) {
				return $http_origin;
			}
			return 'null';
		}
		return '*';
	}

	protected function setAccessControlAllowOriginHeader() {
		$origin = $this->getAllowedOrigin ();
		unset ( $this->headers ['Access-Control-Allow-Origin'] );
		\header ( 'Access-Control-Allow-Origin: ' . $origin, true );
	}

	protected function addOtherHeaders() {
		foreach ( $this->headers as $k => $v ) {
			$this->_header ( $k, $v );
		}
	}

	/**
	 *
	 * @param string $headerField
	 * @param string $value
	 * @param null|boolean $replace
	 */
	public function _header($headerField, $value = null, $replace = null) {
		if (! isset ( $value )) {
			if (isset ( $this->headers [$headerField] )) {
				$value = $this->headers [$headerField];
				unset ( $this->headers [$headerField] );
			} else
				return;
		}
		\header ( trim ( $headerField ) . ": " . trim ( $value ), $replace );
	}

	/**
	 *
	 * @param string $contentType default application/json
	 * @param string $charset default utf8
	 */
	public function _setContentType($contentType = null, $charset = null) {
		$value = $contentType;
		if (isset ( $charset ))
			$value .= "; charset=" . $charset;
		$this->_header ( "Content-type", $value );
	}

	public function cors() {
		$this->setAccessControlAllowOriginHeader ();
		$this->_header ( 'Access-Control-Allow-Credentials' );
		$this->_header ( 'Access-Control-Max-Age' );
		if ($_SERVER ['REQUEST_METHOD'] == 'OPTIONS') {
			if (isset ( $_SERVER ['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] ))
				$this->_header ( 'Access-Control-Allow-Methods' );

			if (isset ( $_SERVER ['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'] )) {
				$this->_header ( 'Access-Control-Allow-Headers', $_SERVER ['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'] );
			} else {
				$this->_header ( 'Access-Control-Allow-Headers', '*' );
			}
			Logger::info ( "Rest", "cors exit normally", "Cors" );
		}
		$this->addOtherHeaders ();
	}

	public static function getRestNamespace() {
		$config = Startup::getConfig ();
		$controllerNS = Startup::getNS('controllers');
		$restNS = $config ["mvcNS"]["rest"]??"";
		return ClassUtils::getNamespaceFromParts ( [ $controllerNS,$restNS ] );
	}

	/**
	 * Adds an unique allowed origin for access control.
	 *
	 * @param string $address
	 */
	public function setAllowedOrigin($address = '*') {
		if ($address !== '*') {
			$this->allowedOrigins = [ $address ];
		} else {
			$this->allowedOrigins = [ ];
		}
	}

	/**
	 * Sets the allowed origins for access control.
	 *
	 * @param array $addresses
	 */
	public function setAllowedOrigins($addresses) {
		$this->allowedOrigins = $addresses;
	}

	/**
	 * Adds an allowed origin for access control.
	 *
	 * @param string $address
	 */
	public function addAllowedOrigin($address) {
		$this->allowedOrigins = [ $address ];
	}

	/**
	 *
	 * @param int $tokenLength
	 */
	public function setTokenLength($tokenLength) {
		$this->tokenLength = $tokenLength;
	}

	/**
	 *
	 * @param mixed $tokenDuration
	 */
	public function setTokenDuration($tokenDuration) {
		$this->tokenDuration = $tokenDuration;
	}
}

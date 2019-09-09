<?php

namespace Ubiquity\controllers\rest;

use Ubiquity\cache\CacheManager;
use Ubiquity\controllers\Controller;
use Ubiquity\controllers\Startup;
use Ubiquity\orm\DAO;
use Ubiquity\utils\base\UString;
use Ubiquity\controllers\Router;

/**
 * Abstract base class for Rest controllers.
 * Ubiquity\controllers\rest$RestController
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.6
 *
 */
abstract class RestBaseController extends Controller {
	use RestControllerUtilitiesTrait;
	protected $config;
	protected $model;
	protected $contentType;
	protected $restCache;
	protected $useValidation = true;

	/**
	 *
	 * @var ResponseFormatter
	 */
	protected $responseFormatter;

	/**
	 *
	 * @var RestServer
	 */
	protected $server;

	public function __construct() {
		if (! \headers_sent ()) {
			@\set_exception_handler ( array ($this,'_errorHandler' ) );
			$this->config = Startup::getConfig ();
			$this->server = $this->_getRestServer ();
			$this->server->cors ();
			$this->responseFormatter = $this->_getResponseFormatter ();
			$this->server->_setContentType ( $this->contentType );
			$this->restCache = CacheManager::getRestCacheController ( \get_class ( $this ) );
		}
		if (! $this->isValid ( Startup::getAction () ))
			$this->onInvalidControl ();
	}

	public function index() {
		$routesPath = Router::getRoutesPathByController ( get_class ( $this ) );
		echo $this->_getResponseFormatter ()->format ( [ "links" => $routesPath ] );
	}

	public function isValid($action) {
		if (isset ( $this->restCache ["authorizations"] )) {
			if (\array_search ( $action, $this->restCache ["authorizations"] ) !== false) {
				return $this->server->isValid ();
			}
		}
		return true;
	}

	/**
	 * Returns true if $action require an authentification with token
	 *
	 * @param string $action
	 * @return boolean
	 */
	protected function requireAuth($action) {
		if (isset ( $this->restCache ["authorizations"] )) {
			return array_search ( $action, $this->restCache ["authorizations"] ) !== false;
		}
		return false;
	}

	public function onInvalidControl() {
		throw new \Exception ( 'HTTP/1.1 401 Unauthorized, you need an access token for this request', 401 );
	}

	/**
	 * Realize the connection to the server
	 * To override in derived classes to define your own authentication
	 */
	public function connect() {
		$resp = $this->server->connect ();
		echo $this->_format ( $resp );
	}

	public function initialize() {
		// $this->connectDb ( $this->config );//To check
	}

	public function finalize() {
		parent::finalize ();
		$this->server->finalizeTokens ();
	}

	public function _errorHandler($e) {
		$this->_setResponseCode ( 200 );
		echo $this->_getResponseFormatter ()->formatException ( $e );
	}

	public function _setResponseCode($value) {
		\http_response_code ( $value );
	}

	/**
	 * Returns a list of objects from the server.
	 *
	 * @param string $condition the sql Where part
	 * @param boolean|string $include if true, loads associate members with associations, if string, example : client.*,commands
	 * @param boolean $useCache
	 */
	public function _get($condition = "1=1", $include = false, $useCache = false) {
		try {
			$condition = $this->getCondition ( $condition );
			$include = $this->getInclude ( $include );
			$useCache = UString::isBooleanTrue ( $useCache );
			$datas = DAO::getAll ( $this->model, $condition, $include, null, $useCache );
			echo $this->_getResponseFormatter ()->get ( $datas );
		} catch ( \Exception $e ) {
			$this->_setResponseCode ( 500 );
			echo $this->_getResponseFormatter ()->formatException ( $e );
		}
	}

	/**
	 * Get the first object corresponding to the $keyValues.
	 *
	 * @param string $keyValues primary key(s) value(s) or condition
	 * @param boolean|string $include if true, loads associate members with associations, if string, example : client.*,commands
	 * @param boolean $useCache if true then response is cached
	 */
	public function _getOne($keyValues, $include = false, $useCache = false) {
		$keyValues = $this->getCondition ( $keyValues );
		$include = $this->getInclude ( $include );
		$useCache = UString::isBooleanTrue ( $useCache );
		$data = DAO::getById ( $this->model, $keyValues, $include, $useCache );
		if (isset ( $data )) {
			$_SESSION ["_restInstance"] = $data;
			echo $this->_getResponseFormatter ()->getOne ( $data );
		} else {
			$this->_setResponseCode ( 404 );
			echo $this->_getResponseFormatter ()->format ( RestError::notFound ( $keyValues, "RestController/getOne" )->asArray () );
		}
	}

	public function _format($arrayMessage) {
		return $this->_getResponseFormatter ()->format ( $arrayMessage );
	}

	/**
	 *
	 * @param string $ids
	 * @param string $member
	 * @param string|boolean $include if true, loads associate members with associations, if string, example : client.*,commands
	 * @param boolean $useCache
	 */
	public function _getManyToOne($ids, $member, $include = false, $useCache = false) {
		$this->getAssociatedMemberValues_ ( $ids, function ($instance, $member, $include, $useCache) {
			return DAO::getManyToOne ( $instance, $member, $include, $useCache );
		}, $member, $include, $useCache, false );
	}

	/**
	 *
	 * @param string $ids
	 * @param string $member
	 * @param string|boolean $include if true, loads associate members with associations, if string, example : client.*,commands
	 * @param boolean $useCache
	 * @throws \Exception
	 */
	public function _getOneToMany($ids, $member, $include = false, $useCache = false) {
		$this->getAssociatedMemberValues_ ( $ids, function ($instance, $member, $include, $useCache) {
			return DAO::getOneToMany ( $instance, $member, $include, $useCache );
		}, $member, $include, $useCache, true );
	}

	/**
	 *
	 * @param string $ids
	 * @param string $member
	 * @param string|boolean $include if true, loads associate members with associations, if string, example : client.*,commands
	 * @param boolean $useCache
	 * @throws \Exception
	 */
	public function _getManyToMany($ids, $member, $include = false, $useCache = false) {
		$this->getAssociatedMemberValues_ ( $ids, function ($instance, $member, $include, $useCache) {
			return DAO::getManyToMany ( $instance, $member, $include, null, $useCache );
		}, $member, $include, $useCache, true );
	}

	/**
	 * Update an instance of $model selected by the primary key $keyValues
	 * Require members values in $_POST array
	 *
	 * @param array $keyValues
	 */
	public function _update(...$keyValues) {
		$instance = DAO::getById ( $this->model, $keyValues, false );
		$this->operate_ ( $instance, function ($instance) {
			$datas = $this->getDatas ();
			$this->_setValuesToObject ( $instance, $datas );
			if ($this->_validateInstance ( $instance, array_keys ( $datas ) )) {
				return $this->updateOperation ( $instance, $datas, true );
			}
			return null;
		}, "updated", "Unable to update the instance", $keyValues );
	}

	/**
	 * Insert a new instance of $model
	 * Require members values in $_POST array
	 */
	public function _add() {
		$model = $this->model;
		$instance = new $model ();
		$this->operate_ ( $instance, function ($instance) {
			$datas = $this->getDatas ();
			$this->_setValuesToObject ( $instance, $datas );
			if ($this->_validateInstance ( $instance, $datas )) {
				return $this->AddOperation ( $instance, $datas, true );
			}
			return null;
		}, "inserted", "Unable to insert the instance", [ ] );
	}

	/**
	 * Delete the instance of $model selected by the primary key $keyValues
	 *
	 * @param array $keyValues
	 */
	public function _delete(...$keyValues) {
		$instance = DAO::getById ( $this->model, $keyValues, false );
		$this->operate_ ( $instance, function ($instance) {
			return DAO::remove ( $instance );
		}, "deleted", "Unable to delete the instance", $keyValues );
	}

	public static function _getApiVersion() {
		return '?';
	}

	/**
	 * Returns the template for creating this type of controller
	 *
	 * @return string
	 */
	public static function _getTemplateFile() {
		return 'restController.tpl';
	}
}

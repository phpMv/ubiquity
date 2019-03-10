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
 * @version 1.0.5
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
			$this->contentType = "application/json";
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

	public function onInvalidControl() {
		throw new \Exception ( 'HTTP/1.1 401 Unauthorized, you need an access token for this request', 401 );
	}

	/**
	 * Realize the connection to the server
	 * To override in derived classes to define your own authentication
	 */
	public function connect() {
		$this->server->connect ( $this );
	}

	public function initialize() {
		$this->connectDb ( $this->config );
	}

	public function finalize() {
		parent::finalize ();
		$this->server->finalizeTokens ();
	}

	public function _errorHandler($e) {
		$code = 500;
		if ($e->getCode () !== 0)
			$code = $e->getCode ();
		$this->_setResponseCode ( $code );
		echo $this->_getResponseFormatter ()->formatException ( $e );
	}

	public function _setResponseCode($value) {
		\http_response_code ( $value );
	}

	/**
	 * Returns a list of objects from the server.
	 *
	 * @param string $condition
	 *        	the sql Where part
	 * @param boolean|string $included
	 *        	if true, loads associate members with associations, if string, example : client.*,commands
	 * @param boolean $useCache
	 */
	public function _get($condition = "1=1", $included = false, $useCache = false) {
		try {
			$condition = \urldecode ( $condition );
			$included = $this->getIncluded ( $included );
			$useCache = UString::isBooleanTrue ( $useCache );
			$datas = DAO::getAll ( $this->model, $condition, $included, null, $useCache );
			echo $this->_getResponseFormatter ()->get ( $datas );
		} catch ( \Exception $e ) {
			$this->_setResponseCode ( 500 );
			echo $this->_getResponseFormatter ()->formatException ( $e );
		}
	}

	/**
	 * Get the first object corresponding to the $keyValues.
	 *
	 * @param string $keyValues
	 *        	primary key(s) value(s) or condition
	 * @param boolean|string $included
	 *        	if true, loads associate members with associations, if string, example : client.*,commands
	 * @param boolean $useCache
	 *        	if true then response is cached
	 */
	public function _getOne($keyValues, $included = false, $useCache = false) {
		$keyValues = \urldecode ( $keyValues );
		$included = $this->getIncluded ( $included );
		$useCache = UString::isBooleanTrue ( $useCache );
		$data = DAO::getOne ( $this->model, $keyValues, $included, null, $useCache );
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
	 * @param array|boolean $included
	 *        	if true, loads associate members with associations, if string, example : client.*,commands
	 * @param boolean $useCache
	 */
	public function _getManyToOne($ids, $member, $included = false, $useCache = false) {
		$this->getAssociatedMemberValues_ ( $ids, function ($instance, $member, $included, $useCache) {
			return DAO::getManyToOne ( $instance, $member, $included, $useCache );
		}, $member, $included, $useCache, false );
	}

	/**
	 *
	 * @param string $ids
	 * @param string $member
	 * @param array|boolean $included
	 *        	if true, loads associate members with associations, if string, example : client.*,commands
	 * @param boolean $useCache
	 * @throws \Exception
	 */
	public function _getOneToMany($ids, $member, $included = false, $useCache = false) {
		$this->getAssociatedMemberValues_ ( $ids, function ($instance, $member, $included, $useCache) {
			return DAO::getOneToMany ( $instance, $member, $included, $useCache );
		}, $member, $included, $useCache, true );
	}

	/**
	 *
	 * @param string $ids
	 * @param string $member
	 * @param array|boolean $included
	 *        	if true, loads associate members with associations, if string, example : client.*,commands
	 * @param boolean $useCache
	 * @throws \Exception
	 */
	public function _getManyToMany($ids, $member, $included = false, $useCache = false) {
		$this->getAssociatedMemberValues_ ( $ids, function ($instance, $member, $included, $useCache) {
			return DAO::getManyToMany ( $instance, $member, $included, null, $useCache );
		}, $member, $included, $useCache, true );
	}

	/**
	 * Update an instance of $model selected by the primary key $keyValues
	 * Require members values in $_POST array
	 *
	 * @param array $keyValues
	 */
	public function _update(...$keyValues) {
		$instance = DAO::getOne ( $this->model, $keyValues );
		$this->operate_ ( $instance, function ($instance) {
			$this->_setValuesToObject ( $instance, $this->getDatas () );
			if ($this->validateInstance ( $instance )) {
				return DAO::update ( $instance );
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
			$this->_setValuesToObject ( $instance, $this->getDatas () );
			if ($this->validateInstance ( $instance )) {
				return DAO::insert ( $instance );
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
		$instance = DAO::getOne ( $this->model, $keyValues );
		$this->operate_ ( $instance, function ($instance) {
			return DAO::remove ( $instance );
		}, "deleted", "Unable to delete the instance", $keyValues );
	}

	public static function _getApiVersion() {
		return '?';
	}
}

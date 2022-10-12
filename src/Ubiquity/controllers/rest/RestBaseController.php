<?php

namespace Ubiquity\controllers\rest;

use Ubiquity\cache\CacheManager;
use Ubiquity\controllers\Controller;
use Ubiquity\controllers\rest\formatters\ResponseFormatter;
use Ubiquity\controllers\rest\traits\RestControllerUtilitiesTrait;
use Ubiquity\controllers\Startup;
use Ubiquity\orm\DAO;
use Ubiquity\utils\base\UString;
use Ubiquity\controllers\Router;
use Ubiquity\orm\OrmUtils;
use Ubiquity\events\EventsManager;
use Ubiquity\events\RestEvents;

/**
 * Abstract base class for Rest controllers.
 * Ubiquity\controllers\rest$RestController
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.8
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
	 * @var formatters\ResponseFormatter
	 */
	protected $responseFormatter;
	/**
	 *
	 * @var formatters\RequestFormatter
	 */
	protected $requestFormatter;

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
			$this->requestFormatter = $this->_getRequestFormatter ();
			$this->server->_setContentType ( $this->contentType );
			$this->restCache = CacheManager::getRestCacheController ( \get_class ( $this ) );
		}
		if (! $this->isValid ( Startup::getAction () ))
			$this->onInvalidControl ();
	}

	public function index() {
		$routesPath = Router::getRoutesPathByController ( get_class ( $this ) );
		echo $this->_getResponseFormatter ()->format ( [ 'links' => $routesPath ] );
	}

	public function isValid($action) {
		if (isset ( $this->restCache ['authorizations'] )) {
			if (\array_search ( $action, $this->restCache ['authorizations'] ) !== false) {
				return $this->server->isValid ( function ($datas = null) use ($action) {
					$this->checkPermissions ( $action, $datas );
				} );
			}
		}
		return true;
	}

	/**
	 * To override in derived classes.
	 * Check if the datas in the authorization token allows access to the action.
	 * If the token is expired, this method is not used.
	 *
	 * @param $action
	 * @param mixed $datas datas in authorization token
	 * @return bool
	 */
	protected function checkPermissions($action, $datas = null) {
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
			return \array_search ( $action, $this->restCache ["authorizations"] ) !== false;
		}
		return false;
	}

	public function onInvalidControl() {
		throw new \Exception ( 'HTTP/1.1 401 Unauthorized, you need an access token for this request', 401 );
	}

	/**
	 * Realize the connection to the server.
	 * To override in derived classes to define your own authentication
	 */
	public function connect() {
		$datas = null;
		$resp = $this->server->connect ( $datas );
		echo $this->_format ( $resp );
	}

	/**
	 * Refresh an active token.
	 * @throws \Ubiquity\exceptions\RestException
	 */
	protected function refreshToken(){
		$resp = $this->server->refreshToken();
		echo $this->_format($resp);
	}

	public function initialize() {
	}

	public function finalize() {
		parent::finalize ();
		$this->server->finalizeTokens ();
	}

	public function _errorHandler($e) {
		$this->_setResponseCode ( Router::getStatusCode() );
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
	public function _get($condition = '1=1', $include = false, $useCache = false) {
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
	 * Returns all the instances from the model $resource.
	 * Query parameters:
	 * - **include**: A string of associated members to load, comma separated (e.g. users,groups,organization...), or a boolean: true for all members, false for none (default: false).
	 * - **filter**: The filter to apply to the query (where part of an SQL query) (default: 1=1).
	 * - **page[number]**: The page to display (in this case, the page size is set to 1).
	 * - **page[size]**: The page size (count of instance per page) (default: 1).
	 */
	public function _getAll() {
		$filter = $this->getCondition ( $this->getRequestParam ( 'filter', '1=1' ) );
		$pages = null;
		if (isset ( $_GET ['page'] )) {
			$pageNumber = $_GET ['page'] ['number'];
			$pageSize = $_GET ['page'] ['size'] ?? 1;
			$pages = $this->generatePagination ( $filter, $pageNumber, $pageSize );
		}
		$datas = DAO::getAll ( $this->model, $filter, $this->getInclude ( $this->getRequestParam ( 'include', false ) ) );
		echo $this->_getResponseFormatter ()->get ( $datas, $pages );
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
			EventsManager::trigger ( RestEvents::BEFORE_UPDATE, $instance, $datas, $this );
			$this->_setValuesToObject ( $instance, $datas );
			if ($this->_validateInstance ( $instance, \array_keys ( $datas ) )) {
				return $this->updateOperation ( $instance, $datas, true );
			}
			return null;
		}, 'updated', 'Unable to update the instance', $keyValues );
	}

	/**
	 * Insert a new instance of $model
	 * Require members values in $_POST array
	 */
	public function _add() {
		$model = $this->model;
		$instance = new $model ();
		$this->operate_ ( $instance, function ($instance) use ($model) {
			$datas = $this->getDatas ();
			EventsManager::trigger ( RestEvents::BEFORE_INSERT, $instance, $datas, $this );
			$this->_setValuesToObject ( $instance, $datas );
			$fields = \array_keys ( OrmUtils::getSerializableFields ( $model ) );
			if ($this->_validateInstance ( $instance, $fields, [ 'id' => false ] )) {
				return $this->addOperation ( $instance, $datas, true );
			}
			return null;
		}, 'inserted', 'Unable to insert the instance', [ ] );
	}

	/**
	 * Returns an associated member value(s).
	 * Query parameters:
	 * - **include**: A string of associated members to load, comma separated (e.g. users,groups,organization...), or a boolean: true for all members, false for none (default: true).
	 *
	 * @param string $id The primary key value(s), if the primary key is composite, use a comma to separate the values (e.g. 1,115,AB)
	 * @param string $member The member to load
	 *
	 */
	public function _getRelationShip($id, $member) {
		$relations = OrmUtils::getAnnotFieldsInRelations ( $this->model );
		if (isset ( $relations [$member] )) {
			$include = $this->getRequestParam ( 'include', true );
			switch ($relations [$member] ['type']) {
				case 'manyToOne' :
					$this->_getManyToOne ( $id, $member, $include );
					break;
				case 'oneToMany' :
					$this->_getOneToMany ( $id, $member, $include );
					break;
				case 'manyToMany' :
					$this->_getManyToMany ( $id, $member, $include );
					break;
			}
		}
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
		}, 'deleted', 'Unable to delete the instance', $keyValues );
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

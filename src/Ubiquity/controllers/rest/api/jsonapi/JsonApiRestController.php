<?php

/**
 * JsonApi implementation
 */
namespace Ubiquity\controllers\rest\api\jsonapi;

use Ubiquity\orm\DAO;
use Ubiquity\controllers\rest\RestError;
use Ubiquity\controllers\Startup;
use Ubiquity\controllers\rest\RestBaseController;
use Ubiquity\utils\http\URequest;
use Ubiquity\orm\OrmUtils;
use Ubiquity\controllers\rest\RestServer;
use Ubiquity\controllers\crud\CRUDHelper;

/**
 * Rest JsonAPI implementation.
 * Ubiquity\controllers\rest\api\jsonapi$JsonApiRestController
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.1.3
 * @since Ubiquity 2.0.11
 *
 */
abstract class JsonApiRestController extends RestBaseController {
	const API_VERSION = 'JsonAPI 1.0';

	protected function _setResource($resource) {
		$modelsNS = $this->config ["mvcNS"] ["models"];
		$this->model = $modelsNS . "\\" . $this->_getResponseFormatter ()->getModel ( $resource );
	}

	protected function _checkResource($resource, $callback) {
		$this->_setResource ( $resource );
		if (class_exists ( $this->model )) {
			$callback ();
		} else {
			$this->_setResponseCode ( 404 );
			$error = new RestError ( 404, "Not existing class", $this->model . " class does not exists!", Startup::getController () . "/" . Startup::getAction () );
			echo $this->_format ( $error->asArray () );
		}
	}

	protected function getDatas() {
		$datas = URequest::getRealInput ();
		if (sizeof ( $datas ) > 0) {
			$datas = current ( array_keys ( $datas ) );
			$datas = json_decode ( $datas, true );
			$attributes = $datas ["data"] ["attributes"] ?? [ ];
			if (isset ( $datas ["data"] ["id"] )) {
				$key = OrmUtils::getFirstKey ( $this->model );
				$attributes [$key] = $datas ["data"] ["id"];
			}
			$this->loadRelationshipsDatas ( $datas, $attributes );
			return $attributes;
		}
		$this->addError ( 204, 'No content', 'The POST request has no content!' );
	}

	protected function loadRelationshipsDatas($datas, &$attributes) {
		if (isset ( $datas ['data'] ['relationships'] )) {
			$relationShips = $datas ['data'] ['relationships'];
			foreach ( $relationShips as $member => $data ) {
				if (isset ( $data ['data'] ['id'] )) {
					$m = OrmUtils::getJoinColumnName ( $this->model, $member );
					$attributes [$m] = $data ['data'] ['id'];
				}
			}
		}
	}

	/**
	 *
	 * @return RestServer
	 */
	protected function getRestServer(): RestServer {
		return new JsonApiRestServer ( $this->config );
	}

	protected function updateOperation($instance, $datas, $updateMany = false) {
		$instance->_new = false;
		return CRUDHelper::update ( $instance, $datas, false, $updateMany );
	}

	protected function AddOperation($instance, $datas, $insertMany = false) {
		$instance->_new = true;
		return CRUDHelper::update ( $instance, $datas, false, $insertMany );
	}

	/**
	 * Route for CORS
	 *
	 * @route("{resource}","methods"=>["options"],"priority"=>3000)
	 */
	public function options(...$resource) {
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\controllers\rest\RestBaseController::index()
	 * @route("links/","methods"=>["get"],"priority"=>3000)
	 */
	public function index() {
		parent::index ();
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\controllers\rest\RestBaseController::connect()
	 * @route("connect/","priority"=>2500)
	 */
	public function connect() {
		parent::connect ();
	}

	/**
	 * Returns all the instances from the model $resource.
	 * Query parameters:
	 * - **include**: A string of associated members to load, comma separated (e.g. users,groups,organization...), or a boolean: true for all members, false for none (default: true).
	 * - **filter**: The filter to apply to the query (where part of an SQL query) (default: 1=1).
	 * - **page[number]**: The page to display (in this case, the page size is set to 1).
	 * - **page[size]**: The page size (count of instance per page) (default: 1).
	 *
	 * @route("{resource}/","methods"=>["get"],"priority"=>0)
	 */
	public function getAll_($resource) {
		$this->_checkResource ( $resource, function () {
			$filter = $this->getCondition ( $this->getRequestParam ( 'filter', '1=1' ) );
			$pages = null;
			if (isset ( $_GET ['page'] )) {
				$pageNumber = $_GET ['page'] ['number'];
				$pageSize = $_GET ['page'] ['size'] ?? 1;
				$pages = $this->generatePagination ( $filter, $pageNumber, $pageSize );
			}
			$datas = DAO::getAll ( $this->model, $filter, $this->getInclude ( $this->getRequestParam ( 'include', true ) ) );
			echo $this->_getResponseFormatter ()->get ( $datas, $pages );
		} );
	}

	/**
	 * Returns an instance of $resource, by primary key $id.
	 *
	 * @param string $resource The resource (model) to use
	 * @param string $id The primary key value(s), if the primary key is composite, use a comma to separate the values (e.g. 1,115,AB)
	 *
	 * @route("{resource}/{id}/","methods"=>["get"],"priority"=>1000)
	 */
	public function getOne_($resource, $id) {
		$this->_checkResource ( $resource, function () use ($id) {
			$this->_getOne ( $id, true, false );
		} );
	}

	/**
	 * Returns an associated member value(s).
	 * Query parameters:
	 * - **include**: A string of associated members to load, comma separated (e.g. users,groups,organization...), or a boolean: true for all members, false for none (default: true).
	 *
	 * @param string $resource The resource (model) to use
	 * @param string $id The primary key value(s), if the primary key is composite, use a comma to separate the values (e.g. 1,115,AB)
	 * @param string $member The member to load
	 *
	 * @route("{resource}/{id}/relationships/{member}/","methods"=>["get"],"priority"=>2000)
	 */
	public function getRelationShip_($resource, $id, $member) {
		$this->_checkResource ( $resource, function () use ($id, $member) {
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
		} );
	}

	/**
	 * Inserts a new instance of $resource.
	 * Data attributes are send in data[attributes] request body (in JSON format)
	 *
	 * @param string $resource The resource (model) to use
	 * @route("{resource}/","methods"=>["post"],"priority"=>0)
	 * @authorization
	 */
	public function add_($resource) {
		$this->_checkResource ( $resource, function () {
			parent::_add ();
		} );
	}

	/**
	 * Updates an existing instance of $resource.
	 * Data attributes are send in data[attributes] request body (in JSON format)
	 *
	 * @param string $resource The resource (model) to use
	 *
	 * @route("{resource}/{id}","methods"=>["patch"],"priority"=>0)
	 * @authorization
	 */
	public function update_($resource, ...$id) {
		$this->_checkResource ( $resource, function () use ($id) {
			if (! $this->hasErrors ()) {
				parent::_update ( ...$id );
			} else {
				echo $this->displayErrors ();
			}
		} );
	}

	/**
	 * Deletes an existing instance of $resource.
	 *
	 * @param string $resource The resource (model) to use
	 * @param string $ids The primary key value(s), if the primary key is composite, use a comma to separate the values (e.g. 1,115,AB)
	 *
	 * @route("{resource}/{id}/","methods"=>["delete"],"priority"=>0)
	 * @authorization
	 */
	public function delete_($resource, ...$id) {
		$this->_checkResource ( $resource, function () use ($id) {
			$this->_delete ( ...$id );
		} );
	}

	/**
	 * Returns the api version.
	 *
	 * @return string
	 */
	public static function _getApiVersion() {
		return self::API_VERSION;
	}

	/**
	 * Returns the template for creating this type of controller
	 *
	 * @return string
	 */
	public static function _getTemplateFile() {
		return 'restApiController.tpl';
	}
}


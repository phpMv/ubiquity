<?php

namespace Ubiquity\controllers\rest;

use Ubiquity\cache\CacheManager;
use Ubiquity\orm\DAO;

/**
 * Abstract base class for Simple Rest controllers.
 * Ubiquity\controllers\rest$SimpleRestController
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.1
 * @since Ubiquity 2.1.1
 */
class SimpleRestController extends RestBaseController implements HasResourceInterface {

	public function initialize() {
		$thisClass = \get_class ( $this );
		if (! isset ( $this->model ))
			$this->model = CacheManager::getRestResource ( $thisClass );
		if (! isset ( $this->model )) {
			$modelsNS = $this->config ["mvcNS"] ["models"];
			$this->model = $modelsNS . "\\" . $this->_getResponseFormatter ()->getModel ( $thisClass );
		}
		parent::initialize ();
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\controllers\rest\RestBaseController::index()
	 * @route("/links","methods"=>["get"],"priority"=>3000)
	 */
	public function index() {
		parent::index ();
	}

	/**
	 * Returns all the instances from the model $this->model.
	 * Query parameters:
	 * - **include**: A string of associated members to load, comma separated (e.g. users,groups,organization...), or a boolean: true for all members, false for none (default: true).
	 * - **filter**: The filter to apply to the query (where part of an SQL query) (default: 1=1).
	 * - **page[number]**: The page to display (in this case, the page size is set to 1).
	 * - **page[size]**: The page size (count of instance per page) (default: 1).
	 *
	 * @route("/","methods"=>["get"],"priority"=>0)
	 */
	public function getAll_() {
		$filter = $this->getCondition($this->getRequestParam ( 'filter', '1=1' ));
		$pages = null;
		if (isset ( $_GET ['page'] )) {
			$pageNumber = $_GET ['page'] ['number'];
			$pageSize = $_GET ['page'] ['size'] ?? 1;
			$pages = $this->generatePagination ( $filter, $pageNumber, $pageSize );
		}
		$datas = DAO::getAll ( $this->model, $filter, $this->getInclude ( $this->getRequestParam ( 'include', true ) ) );
		echo $this->_getResponseFormatter ()->get ( $datas, $pages );
	}

	/**
	 * Get the first object corresponding to the $keyValues
	 * Query parameters:
	 * - **include**: A string of associated members to load, comma separated (e.g.
	 * users,groups,organization...), or a boolean: true for all members, false for none (default: true).
	 *
	 * @param string $id primary key(s) value(s) or condition
	 * @route("{id}/","methods"=>["get"],"priority"=>1000)
	 */
	public function getOne($id) {
		$this->_getOne ( $id, $this->getRequestParam ( 'include', true ) );
	}

	/**
	 * Update an instance of $model selected by the primary key $keyValues
	 * Require members values in $_POST array
	 * Requires an authorization with access token
	 *
	 * @param array $keyValues
	 * @authorization
	 * @route("/{keyValues}","methods"=>["patch"],"priority"=>0)
	 */
	public function update(...$keyValues) {
		$this->_update ( ...$keyValues );
	}

	/**
	 *
	 * @route("/","methods"=>["options"],"priority"=>3000)
	 */
	public function options(...$resource) {
	}
	
	/**
	 * Insert a new instance of $model
	 * Require members values in $_POST array
	 * Requires an authorization with access token
	 *
	 * @authorization
	 * @route("/","methods"=>["post"],"priority"=>0)
	 */
	public function add() {
		$this->_add ();
	}

	/**
	 * Delete the instance of $model selected by the primary key $keyValues
	 * Requires an authorization with access token
	 *
	 * @param array $keyValues
	 * @route("/{keyValues}","methods"=>["delete"],"priority"=>30)
	 * @authorization
	 */
	public function delete(...$keyValues) {
		$this->_delete ( ...$keyValues );
	}
}

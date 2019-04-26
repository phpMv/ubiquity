<?php

/**
 * Rest part
 */
namespace Ubiquity\controllers\rest;

use Ubiquity\cache\CacheManager;
use Ubiquity\orm\DAO;

/**
 * Abstract base class for Rest controllers.
 * Ubiquity\controllers\rest$RestController
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.4
 *
 */
abstract class RestController extends RestBaseController implements HasResourceInterface {

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
	 * Returns all objects for the resource $model
	 */
	public function _index() {
		$datas = DAO::getAll ( $this->model );
		echo $this->_getResponseFormatter ()->get ( $datas );
	}

	/**
	 * Returns a list of objects from the server
	 *
	 * @param string $condition the sql Where part
	 * @param boolean|string $included if true, loads associate members with associations, if string, example : client.*,commands
	 * @param boolean $useCache
	 * @route("methods"=>["get"])
	 */
	public function get($condition = "1=1", $included = false, $useCache = false) {
		$this->_get ( $condition, $included, $useCache );
	}

	/**
	 * Get the first object corresponding to the $keyValues
	 *
	 * @param string $keyValues primary key(s) value(s) or condition
	 * @param boolean|string $included if true, loads associate members with associations, if string, example : client.*,commands
	 * @param boolean $useCache if true then response is cached
	 * @route("methods"=>["get"])
	 */
	public function getOne($keyValues, $included = false, $useCache = false) {
		$this->_getOne ( $keyValues, $included, $useCache );
	}

	/**
	 * Update an instance of $model selected by the primary key $keyValues
	 * Require members values in $_POST array
	 * Requires an authorization with access token
	 *
	 * @param array $keyValues
	 * @authorization
	 * @route("methods"=>["patch"])
	 */
	public function update(...$keyValues) {
		$this->_update ( ...$keyValues );
	}

	/**
	 * Insert a new instance of $model
	 * Require members values in $_POST array
	 * Requires an authorization with access token
	 *
	 * @authorization
	 * @route("methods"=>["post"])
	 */
	public function add() {
		$this->_add ();
	}

	/**
	 * Delete the instance of $model selected by the primary key $keyValues
	 * Requires an authorization with access token
	 *
	 * @param array $keyValues
	 * @route("methods"=>["delete"],"priority"=>30)
	 * @authorization
	 */
	public function delete(...$keyValues) {
		$this->_delete ( ...$keyValues );
	}
}

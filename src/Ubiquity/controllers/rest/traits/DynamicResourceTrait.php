<?php

namespace Ubiquity\controllers\rest\traits;

use Ubiquity\controllers\crud\CRUDHelper;
use Ubiquity\controllers\rest\RestError;
use Ubiquity\controllers\Startup;
use Ubiquity\orm\DAO;
use Ubiquity\orm\OrmUtils;

/**
 * Ubiquity\controllers\rest\traits$DynamicResourceTrait
 * This class is part of Ubiquity
 *
 * @author jc
 * @version 1.0.0
 * 
 * @property string $model
 */
trait DynamicResourceTrait {
	
	abstract protected function _getResponseFormatter();
	
	abstract public function _setResponseCode($value);
	
	abstract public function _format($arrayMessage);
	
	abstract public function _getAll();
	
	abstract public function _getRelationShip($id, $member);
	
	abstract public function _getOne($keyValues, $include = false, $useCache = false);

	abstract protected function getRequestParam($param, $default);
	
	abstract protected function hasErrors();
	
	abstract protected function displayErrors();
	
	abstract public function _delete(...$keyValues);
	
	protected function updateOperation($instance, $datas, $updateMany = false) {
		$instance->_new = false;
		return CRUDHelper::update ( $instance, $datas, false, $updateMany );
	}

	protected function addOperation($instance, $datas, $insertMany = false) {
		$instance->_new = true;
		return CRUDHelper::update ( $instance, $datas, false, $insertMany );
	}

	protected function _setResource($resource) {
		$modelsNS = Startup::getNS('models');
		$this->model = $modelsNS . $this->_getResponseFormatter ()->getModel ( $resource );
	}

	protected function _checkResource($resource, $callback) {
		$this->_setResource ( $resource );
		if (\class_exists ( $this->model )) {
			$callback ();
		} else {
			$this->_setResponseCode ( 404 );
			$error = new RestError ( 404, "Not existing class", $this->model . " class does not exists!", Startup::getController () . "/" . Startup::getAction () );
			echo $this->_format ( $error->asArray () );
		}
	}

	/**
	 * Returns all the instances from the model $resource.
	 */
	public function getAll_($resource) {
		$this->_checkResource ( $resource, function () {
			$this->_getAll ();
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
	 */
	public function getRelationShip_($resource, $id, $member) {
		$this->_checkResource ( $resource, function () use ($id, $member) {
			$this->_getRelationShip ( $id, $member );
		} );
	}

	/**
	 * Returns an instance of $resource, by primary key $id.
	 *
	 * @param string $resource The resource (model) to use
	 * @param string $id The primary key value(s), if the primary key is composite, use a comma to separate the values (e.g. 1,115,AB)
	 *
	 */
	public function getOne_($resource, $id) {
		$this->_checkResource ( $resource, function () use ($id) {
			$this->_getOne ( $id, $this->getRequestParam ( 'include', false ), false );
		} );
	}

	/**
	 * Inserts a new instance of $resource.
	 * Data attributes are send in data[attributes] request body (in JSON format)
	 *
	 * @param string $resource The resource (model) to use
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
	 */
	public function delete_($resource, ...$id) {
		$this->_checkResource ( $resource, function () use ($id) {
			$this->_delete ( ...$id );
		} );
	}
}
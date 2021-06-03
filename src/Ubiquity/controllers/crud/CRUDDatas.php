<?php

namespace Ubiquity\controllers\crud;

use Ubiquity\orm\DAO;
use Ubiquity\orm\OrmUtils;

/**
 * The base class for displaying datas in CRUD controllers
 * Ubiquity\controllers\crud$CRUDDatas
 * This class is part of Ubiquity
 *
 * @author jc
 * @version 1.0.0
 *
 */
class CRUDDatas {
	
	protected $controller;
	
	public function __construct($controller) {
		$this->controller = $controller;
	}

	/**
	 * Returns the table names to display in the left admin menu
	 */
	public function getTableNames() {
		return DAO::$db->getTablesName ();
	}

	/**
	 * Returns the fields to display in the showModel action for $model (DataTable)
	 *
	 * @param string $model
	 */
	public function getFieldNames($model) {
		return OrmUtils::getSerializableMembers ( $model );
	}

	/**
	 * Returns the fields to update in the edit an new action for $model
	 *
	 * @param string $model
	 * @param object $instance
	 */
	public function getFormFieldNames($model, $instance) {
		return OrmUtils::getFormAllFields ( $model );
	}

	/**
	 * Returns the fields to use in search queries
	 *
	 * @param string $model
	 */
	public function getSearchFieldNames($model) {
		return OrmUtils::getSerializableFields ( $model );
	}

	/**
	 * Returns the fields for displaying an instance of $model (DataElement)
	 *
	 * @param string $model
	 */
	public function getElementFieldNames($model) {
		return OrmUtils::getMembers ( $model );
	}

	/**
	 * Returns a (filtered) list of $fkClass objects to display in an html list
	 *
	 * @param string $fkClass
	 * @param object $instance
	 * @param string $member The member associated with a manyToMany relation
	 * @return array
	 */
	public function getManyToManyDatas($fkClass, $instance, $member) {
		return DAO::getAll ( $fkClass, "", false );
	}

	/**
	 * Returns a list (filtered) of $fkClass objects to display in an html list
	 *
	 * @param string $fkClass
	 * @param object $instance
	 * @param string $member The member associated with a manyToOne relation
	 * @return array
	 */
	public function getManyToOneDatas($fkClass, $instance, $member) {
		return DAO::getAll ( $fkClass, "", false );
	}

	/**
	 * Returns a list (filtered) of $fkClass objects to display in an html list
	 *
	 * @param string $fkClass
	 * @param object $instance
	 * @param string $member The member associated with a oneToMany relation
	 * @return array
	 */
	public function getOneToManyDatas($fkClass, $instance, $member) {
		return DAO::getAll ( $fkClass, "", false );
	}

	/**
	 *
	 * @return boolean
	 */
	public function getUpdateOneToManyInForm() {
		return false;
	}

	/**
	 *
	 * @return boolean
	 */
	public function getUpdateManyToManyInForm() {
		return true;
	}

	/**
	 *
	 * @return boolean
	 */
	public function getUpdateManyToOneInForm() {
		return true;
	}

	/**
	 * Defines whether the refresh is partial or complete after an instance update
	 *
	 * @return boolean
	 */
	public function refreshPartialInstance() {
		return true;
	}

	/**
	 * Adds a condition for filtering the instances displayed in dataTable
	 * Return 1=1 by default
	 *
	 * @param string $model
	 * @return string
	 */
	public function _getInstancesFilter($model) {
		return "1=1";
	}
}

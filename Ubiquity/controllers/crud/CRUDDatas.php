<?php
namespace Ubiquity\controllers\crud;
use Ubiquity\orm\DAO;
use Ubiquity\orm\OrmUtils;

/**
 * The base class for displaying datas in CRUD controllers
 * @author jc
 *
 */
class CRUDDatas {

	/**
	 * Returns the table names to display in the left admin menu
	 */
	public function getTableNames(){
		return DAO::$db->getTablesName();
	}

	/**
	 * Returns the fields to display in the showModel action for $model (DataTable)
	 * @param string $model
	 */
	public function getFieldNames($model){
		return OrmUtils::getSerializableFields($model);
	}

	/**
	 * Returns the fields to update in the edit an new action for $model
	 * @param string $model
	 */
	public function getFormFieldNames($model){
		return OrmUtils::getSerializableFields($model);
	}
	
	/**
	 * Returns the fields to use in search queries
	 * @param string $model
	 */
	public function getSearchFieldNames($model){
		return OrmUtils::getSerializableFields($model);
	}
	
	/**
	 * Returns the fields for displaying an instance of $model (DataElement)
	 * @param string $model
	 */
	public function getElementFieldNames($model){
		return OrmUtils::getMembers($model);
	}

	/**
	 * Returns a list of $fkClass objects to select a value for $member
	 * @param string $fkClass
	 * @param object $instance
	 * @param string $member
	 * @return array
	 */
	public function getManyToManyDatas($fkClass,$instance,$member){
		return DAO::getAll($fkClass);
	}

	/**
	 * @return boolean
	 */
	public function getUpdateOneToManyInForm() {
		return false;
	}

	/**
	 * @return boolean
	 */
	public function getUpdateManyToManyInForm() {
		return true;
	}

	/**
	 * @return boolean
	 */
	public function getUpdateManyToOneInForm() {
		return true;
	}
	
	/**
	 * Defines whether the refresh is partial or complete after an instance update
	 * @return boolean
	 */
	public function refreshPartialInstance(){
		return true;
	}
}

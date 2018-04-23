<?php
namespace Ubiquity\controllers\admin;
use Ubiquity\orm\DAO;
use Ubiquity\orm\OrmUtils;

/**
 * The base class for displaying datas in UbiquityMyAdminController
 * @author jc
 *
 */
class UbiquityMyAdminData {

	/**
	 * Returns the table names to display in the left admin menu
	 */
	public function getTableNames(){
		return DAO::$db->getTablesName();
	}

	/**
	 * Returns the fields to display in the showModel action for $model
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
}

<?php
namespace micro\controllers\admin;
use micro\orm\DAO;
use micro\orm\OrmUtils;

/**
 * The base class for displaying datas in UbiquityMyAdminController
 * @author jc
 *
 */
class UbiquityMyAdminData {

	/**
	 * Returns the table names to display in the left menu
	 */
	public function getTableNames(){
		return DAO::$db->getTablesName();
	}

	/**
	 * Returns the fields to display in the showTable action for $model
	 * @param string $model
	 */
	public function getFieldNames($model){
		return OrmUtils::getSerializableFields($model);
	}

	public function getFormFieldNames($model){
		return OrmUtils::getSerializableFields($model);
	}

	public function getManyToManyDatas($fkClass,$instance,$member){
		return DAO::getAll($fkClass);
	}

	public function getUpdateOneToManyInForm() {
		return false;
	}

	public function getUpdateManyToManyInForm() {
		return true;
	}

	public function getUpdateManyToOneInForm() {
		return true;
	}
}

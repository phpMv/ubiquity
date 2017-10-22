<?php
namespace micro\controllers\admin;
use micro\orm\DAO;
use micro\orm\OrmUtils;

class UbiquityMyAdminData {
	public function getTableNames(){
		return DAO::$db->getTablesName();
	}

	public function getFieldNames($model){
		return OrmUtils::getSerializableFields($model);
	}

	public function getFormFieldNames($model){
		return OrmUtils::getSerializableFields($model);
	}
}
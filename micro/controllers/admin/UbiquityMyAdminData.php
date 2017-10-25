<?php
namespace micro\controllers\admin;
use micro\orm\DAO;
use micro\orm\OrmUtils;

class UbiquityMyAdminData {
	protected $updateOneToManyInForm;
	protected $updateManyToManyInForm;
	protected $updateManyToOneInForm;

	public function __construct(){
		$this->updateOneToManyInForm=false;
		$this->updateManyToManyInForm=true;
		$this->updateManyToOneInForm=true;
	}

	public function getTableNames(){
		return DAO::$db->getTablesName();
	}

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
		return $this->updateOneToManyInForm;
	}

	public function setUpdateOneToManyInForm($updateOneToManyInForm) {
		$this->updateOneToManyInForm=$updateOneToManyInForm;
		return $this;
	}

	public function getUpdateManyToManyInForm() {
		return $this->updateManyToManyInForm;
	}

	public function setUpdateManyToManyInForm($updateManyToManyInForm) {
		$this->updateManyToManyInForm=$updateManyToManyInForm;
		return $this;
	}

	public function getUpdateManyToOneInForm() {
		return $this->updateManyToOneInForm;
	}

	public function setUpdateManyToOneInForm($updateManyToOneInForm) {
		$this->updateManyToOneInForm=$updateManyToOneInForm;
		return $this;
	}



}

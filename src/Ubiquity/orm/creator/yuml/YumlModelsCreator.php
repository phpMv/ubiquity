<?php
namespace Ubiquity\orm\creator\yuml;

use Ubiquity\orm\creator\ModelsCreator;


class YumlModelsCreator extends ModelsCreator{
	/**
	 * @var YumlParser
	 */
	private $yumlParser;

	protected function init($config){
		parent::init($config);
	}

	public function initYuml($yumlString) {
		$this->yumlParser=new YumlParser($yumlString);
	}


	protected function getTablesName(){
		return $this->yumlParser->getTableNames();
	}

	protected function getFieldsInfos($tableName) {
		$fieldsInfos=array();
		$fields = $this->yumlParser->getFields($tableName);
		foreach ($fields as $field) {
			$fieldsInfos[$field['name']] = ["Type"=>$field['type'],"Nullable"=>(isset($field["null"]) && $field["null"]),"Email" => $this->fieldIsEmail($field['Field'])];
		}
		return $fieldsInfos;
	}

    protected function fieldIsEmail($field)
    {
        $possibleColumnNames = array("email", "mail", "courrierelectronique", "ecourrier", "mailaddresse", "mailaddresse", "mailadress", "mailadresse");
        $res = false;

        if (in_array($field, $possibleColumnNames)) {
            $res = true;
        }
        return $res;
    }

	protected function getPrimaryKeys($tableName){
		return $this->yumlParser->getPrimaryKeys($tableName);
	}

	protected function getForeignKeys($tableName,$pkName){
		return $this->yumlParser->getForeignKeys($tableName);
	}
}

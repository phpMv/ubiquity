<?php
namespace Ubiquity\orm\creator\database;

use Ubiquity\orm\creator\ModelsCreator;


class DbModelsCreator extends ModelsCreator{
	private $pdoObject;

	protected function init($config){
		parent::init($config);
		$this->connect($config["database"]);
	}
	/**
	 * Réalise la connexion à la base de données
	 */
	private function connect($config) {
		try {
			$this->pdoObject = new \PDO(
					$config["type"].':host=' . $config["serverName"] . ';dbname='
					. $config["dbName"] . ';port=' . $config["port"],
					$config["user"], $config["password"]);
			$this->pdoObject->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
			$this->pdoObject->exec("SET CHARACTER SET utf8");

		} catch (\PDOException $e) {
			print "Error!: " . $e->getMessage() . "<br/>";
		}
	}


	protected function getTablesName(){
		$sql = 'SHOW TABLES';
			$query = $this->pdoObject->query($sql);
			return $query->fetchAll(\PDO::FETCH_COLUMN);
	}

	protected function getFieldsInfos($tableName) {
		$fieldsInfos=array();
		$recordset = $this->pdoObject->query("SHOW COLUMNS FROM `{$tableName}`");
		$fields = $recordset->fetchAll(\PDO::FETCH_ASSOC);
		foreach ($fields as $field) {
			$fieldsInfos[$field['Field']] = ["Type"=>$field['Type'],"Nullable"=>$field["Null"]];
		}
		return $fieldsInfos;
	}

	protected function getPrimaryKeys($tableName){
		$fieldkeys=array();
		$recordset = $this->pdoObject->query("SHOW KEYS FROM `{$tableName}` WHERE Key_name = 'PRIMARY'");
		$keys = $recordset->fetchAll(\PDO::FETCH_ASSOC);
		foreach ($keys as $key) {
			$fieldkeys[] = $key['Column_name'];
		}
		return $fieldkeys;
	}

	protected function getForeignKeys($tableName,$pkName){
		$recordset = $this->pdoObject->query("SELECT *
												FROM
												 information_schema.KEY_COLUMN_USAGE
												WHERE
												 REFERENCED_TABLE_NAME = '".$tableName."'
												 AND REFERENCED_COLUMN_NAME = '".$pkName."'
												 AND TABLE_SCHEMA = '".$this->config["dbName"]."';");
		return $recordset->fetchAll(\PDO::FETCH_ASSOC);
	}
}

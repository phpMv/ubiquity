<?php
use micro\orm\creator\Model;
use micro\orm\creator\Member;
use micro\orm\Reflexion;

class ModelsCreator {
	private static $config;
	private static $pdoObject;
	private static $tables=array();
	private static $classes=array();
	/**
	 * Réalise la connexion à la base de données
	 */
	private static function connect() {
		try {
			self::$pdoObject = new \PDO(
					'mysql:host=' . self::$config["serverName"] . ';dbname='
					. self::$config["dbName"] . ';port:' . self::$config["port"],
					self::$config["user"], self::$config["password"]);
			self::$pdoObject->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
			self::$pdoObject->exec("SET CHARACTER SET utf8");

		} catch (\PDOException $e) {
			print "Error!: " . $e->getMessage() . "<br/>";
			die();
		}
	}
	public static function create(){
		$config=require_once 'app/config.php';
		self::$config=$config["database"];
		self::connect();
		self::$tables=self::getTablesName();
		mkdir("app/models");
		new Reflexion();
		foreach (self::$tables as $table){
			$class=new Model($table);
			$fields=self::getFieldsName($table);
			$keys=self::getPrimaryKeys($table);
			foreach ($fields as $field){
				$member=new Member($field);
				if(in_array($field, $keys)){
					$member->setPrimary();
				}
				$class->addMember($member);
			}
			self::$classes[$table]=$class;
		}
		self::createRelations();
		foreach (self::$classes as $table=>$class){
			self::writeFile("app/models/".$table.".php", $class);
		}
	}

	private static function createRelations(){
		foreach (self::$classes as $table=>$class){
			$keys=self::getPrimaryKeys($table);
			foreach ($keys as $key){
				$fks=self::getForeignKeys($table, $key);
				foreach ($fks as $fk){
					$field=strtolower($table);
					$fkTable=$fk["TABLE_NAME"];
					self::$classes[$table]->addOneToMany($fkTable."s",$table, self::$classes[$fkTable]->getName());
					self::$classes[$fkTable]->addManyToOne($field, $fk["COLUMN_NAME"], $class->getName());
				}
			}
		}
	}

	private static function getTablesName(){
		$sql = 'SHOW TABLES';
			$query = self::$pdoObject->query($sql);
			return $query->fetchAll(PDO::FETCH_COLUMN);
	}

	private static function getFieldsName($tableName) {
		$fieldNames=array();
		$recordset = self::$pdoObject->query("SHOW COLUMNS FROM `{$tableName}`");
		$fields = $recordset->fetchAll(PDO::FETCH_ASSOC);
		foreach ($fields as $field) {
			$fieldNames[] = $field['Field'];
		}
		var_dump($fieldNames);
		return $fieldNames;
	}

	private static function getPrimaryKeys($tableName){
		$fieldkeys=array();
		$recordset = self::$pdoObject->query("SHOW KEYS FROM `{$tableName}` WHERE Key_name = 'PRIMARY'");
		$keys = $recordset->fetchAll(PDO::FETCH_ASSOC);
		foreach ($keys as $key) {
			$fieldkeys[] = $key['Column_name'];
		}
		return $fieldkeys;
	}

	private static function getForeignKeys($tableName,$pkName){
		$recordset = self::$pdoObject->query("SELECT *
												FROM
												 information_schema.KEY_COLUMN_USAGE
												WHERE
												 REFERENCED_TABLE_NAME = '".$tableName."'
												 AND REFERENCED_COLUMN_NAME = '".$pkName."'
												 AND TABLE_SCHEMA = '".self::$config["dbName"]."';");
		return $recordset->fetchAll(PDO::FETCH_ASSOC);
	}

	private static function writeFile($filename,$data){
		return file_put_contents($filename,$data);
	}
}
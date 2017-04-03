<?php
use micro\orm\creator\Model;
use micro\orm\creator\Member;
use micro\orm\Reflexion;
use micro\controllers\Autoloader;

class ModelsCreator {
	private static $config;
	private static $pdoObject;
	private static $tables=array();
	private static $classes=array();

	private static function init(){
		require_once 'app/micro/controllers/Autoloader.php';
		$config=require_once 'app/config.php';
		Autoloader::register($config);
		self::$config=$config["database"];
		self::connect();
	}
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

	public static function create($singleTable=null){
		self::init();
		self::$tables=self::getTablesName();
		if(!is_dir("app/models/runtime"))
			mkdir("app/models/runtime",0777,true);
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
		if(isset($singleTable)){
			self::createOneClass($singleTable);
		}else{
			foreach (self::$classes as $table=>$class){
				$name=$class->getName();
				echo "Creating the {$name} class\n";
				self::writeFile("app/models/".$name.".php", $class);
			}
		}
	}

	private static function createOneClass($singleTable){
		if(isset(self::$classes[$singleTable])){
			$class=self::$classes[$singleTable];
			echo "Creating the {$class->getName()} class\n";
			self::writeFile("app/models/".$singleTable.".php", $class);
		}else{
			echo "The {$singleTable} table does not exist in the database\n";
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
		self::createManyToMany();
	}

	private static function createManyToMany(){
		foreach (self::$classes as $table=>$class){
			if($class->isAssociation()===true){
				$members=$class->getManyToOneMembers();
				if(sizeof($members)==2){
					$manyToOne1=$members[0]->getManyToOne();
					$manyToOne2=$members[1]->getManyToOne();
					$table1=strtolower($manyToOne1->className);
					$table2=strtolower($manyToOne2->className);
					$class1=self::$classes[$table1];
					$class1->addManyToMany($table2."s", $manyToOne2->className, $table1."s", $table);
					$class1->removeMember($table."s");
					$class2=self::$classes[$table2];
					$class2->addManyToMany($table1."s", $manyToOne1->className, $table2."s", $table);
					$class2->removeMember($table."s");
					unset(self::$classes[$table]);
				}else{
					return;
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

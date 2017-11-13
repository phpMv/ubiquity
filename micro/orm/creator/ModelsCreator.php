<?php
namespace micro\orm\creator;

use micro\orm\creator\Model;
use micro\orm\creator\Member;
use micro\annotations\JoinColumnAnnotation;
use micro\cache\CacheManager;
use micro\controllers\Startup;
use micro\utils\FsUtils;


class ModelsCreator {
	private static $config;
	private static $pdoObject;
	private static $tables=array();
	private static $classes=array();

	private static function init($config){
		self::$config=$config["database"];
		self::connect($config["database"]);
	}
	/**
	 * Réalise la connexion à la base de données
	 */
	private static function connect($config) {
		try {
			self::$pdoObject = new \PDO(
					$config["type"].':host=' . $config["serverName"] . ';dbname='
					. $config["dbName"] . ';port:' . $config["port"],
					$config["user"], $config["password"]);
			self::$pdoObject->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
			self::$pdoObject->exec("SET CHARACTER SET utf8");

		} catch (\PDOException $e) {
			print "Error!: " . $e->getMessage() . "<br/>";
			die();
		}
	}

	public static function create($config,$initCache=true,$singleTable=null){
		self::init($config);
		$modelsDir=Startup::getModelsCompletePath();
		if(FsUtils::safeMkdir($modelsDir)){
			self::$tables=self::getTablesName();
			CacheManager::checkCache($config);

			foreach (self::$tables as $table){
				$class=new Model($table,$config["mvcNS"]["models"]);
				$fieldsInfos=self::getFieldsInfos($table);
				$keys=self::getPrimaryKeys($table);
				foreach ($fieldsInfos as $field=>$info){
					$member=new Member($field);
					if(in_array($field, $keys)){
						$member->setPrimary();
					}
					$member->setDbType($info);
					$class->addMember($member);
				}
				self::$classes[$table]=$class;
			}
			self::createRelations();
			if(isset($singleTable)){
				self::createOneClass($singleTable,$modelsDir);
			}else{
				foreach (self::$classes as $table=>$class){
					$name=$class->getSimpleName();
					echo "Creating the {$name} class\n";
					self::writeFile($modelsDir.DS.$name.".php", $class);
				}
			}
			if($initCache===true){
				CacheManager::initCache($config,"models");
			}
		}
	}

	private static function createOneClass($singleTable,$modelsDir){
		if(isset(self::$classes[$singleTable])){
			$class=self::$classes[$singleTable];
			echo "Creating the {$class->getName()} class\n";
			self::writeFile($modelsDir.DS.$singleTable.".php", $class);
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

	private static function getTableName($classname){
		$posSlash=strrpos($classname, '\\');
		$tablename=substr($classname,  $posSlash+ 1);
		return lcfirst($tablename);
	}

	private static function createManyToMany(){
		foreach (self::$classes as $table=>$class){
			if($class->isAssociation()===true){
				$members=$class->getManyToOneMembers();
				if(sizeof($members)==2){
					$manyToOne1=$members[0]->getManyToOne();
					$manyToOne2=$members[1]->getManyToOne();
					$table1=self::getTableName($manyToOne1->className);
					$table2=self::getTableName($manyToOne2->className);
					$class1=self::$classes[$table1];
					$class2=self::$classes[$table2];
					$joinTable1=self::getJoinTableArray($class1, $manyToOne1);
					$joinTable2=self::getJoinTableArray($class2, $manyToOne2);
					$class1->addManyToMany($table2."s", $manyToOne2->className, $table1."s", $table,$joinTable1,$joinTable2);
					$class1->removeMember($table."s");

					$class2->addManyToMany($table1."s", $manyToOne1->className, $table2."s", $table,$joinTable2,$joinTable1);
					$class2->removeMember($table."s");
					unset(self::$classes[$table]);
				}else{
					return;
				}
			}
		}
	}

	private static function getJoinTableArray(Model $class,JoinColumnAnnotation $joinColumn){
		$pk=$class->getPrimaryKey();
		$fk=$joinColumn->name;
		$dFk=$class->getDefaultFk();
		if($fk!==$dFk){
			if($pk!==null && $fk!==null && $pk!==null)
				return ["name"=>$fk, "referencedColumnName"=>$pk];
		}
		return [];
	}

	private static function getTablesName(){
		$sql = 'SHOW TABLES';
			$query = self::$pdoObject->query($sql);
			return $query->fetchAll(\PDO::FETCH_COLUMN);
	}

	private static function getFieldsInfos($tableName) {
		$fieldsInos=array();
		$recordset = self::$pdoObject->query("SHOW COLUMNS FROM `{$tableName}`");
		$fields = $recordset->fetchAll(\PDO::FETCH_ASSOC);
		foreach ($fields as $field) {
			$fieldsInos[$field['Field']] = ["Type"=>$field['Type'],"Nullable"=>$field["Null"]];
		}
		return $fieldsInos;
	}

	private static function getPrimaryKeys($tableName){
		$fieldkeys=array();
		$recordset = self::$pdoObject->query("SHOW KEYS FROM `{$tableName}` WHERE Key_name = 'PRIMARY'");
		$keys = $recordset->fetchAll(\PDO::FETCH_ASSOC);
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
		return $recordset->fetchAll(\PDO::FETCH_ASSOC);
	}

	private static function writeFile($filename,$data){
		return file_put_contents($filename,$data);
	}
}

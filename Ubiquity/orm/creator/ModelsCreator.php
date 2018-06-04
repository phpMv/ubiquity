<?php
namespace Ubiquity\orm\creator;

use Ubiquity\annotations\JoinColumnAnnotation;
use Ubiquity\cache\CacheManager;
use Ubiquity\controllers\Startup;
use Ubiquity\utils\base\UFileSystem;


abstract class ModelsCreator {
	protected $config;
	protected $tables=array();
	protected $classes=array();

	protected function init($config){
		$this->config=$config["database"];
	}

	public function create($config,$initCache=true,$singleTable=null){
		$this->init($config);
		$modelsDir=Startup::getModelsCompletePath();
		if(UFileSystem::safeMkdir($modelsDir)){
			$this->tables=$this->getTablesName();
			CacheManager::checkCache($config);

			foreach ($this->tables as $table){
				$class=new Model($table,$config["mvcNS"]["models"]);
				$fieldsInfos=$this->getFieldsInfos($table);
				$keys=$this->getPrimaryKeys($table);
				foreach ($fieldsInfos as $field=>$info){
					$member=new Member($field);
					if(in_array($field, $keys)){
						$member->setPrimary();
					}
					$member->setDbType($info);
					$class->addMember($member);
				}
				$this->classes[$table]=$class;
			}
			$this->createRelations();
			if(isset($singleTable)){
				$this->createOneClass($singleTable,$modelsDir);
			}else{
				foreach ($this->classes as $table=>$class){
					$name=$class->getSimpleName();
					echo "Creating the {$name} class\n";
					$this->writeFile($modelsDir.DS.$name.".php", $class);
				}
			}
			if($initCache===true){
				CacheManager::initCache($config,"models");
			}
		}
	}

	protected function createOneClass($singleTable,$modelsDir){
		if(isset($this->classes[$singleTable])){
			$class=$this->classes[$singleTable];
			echo "Creating the {$class->getName()} class\n";
			$this->writeFile($modelsDir.DS.$class->getSimpleName().".php", $class);
		}else{
			echo "The {$singleTable} table does not exist in the database\n";
		}
	}

	protected function createRelations(){
		foreach ($this->classes as $table=>$class){
			$keys=$this->getPrimaryKeys($table);
			foreach ($keys as $key){
				$fks=$this->getForeignKeys($table, $key);
				foreach ($fks as $fk){
					$field=strtolower($table);
					$fkTable=$fk["TABLE_NAME"];
					$this->classes[$table]->addOneToMany($fkTable."s",$table, $this->classes[$fkTable]->getName());
					$this->classes[$fkTable]->addManyToOne($field, $fk["COLUMN_NAME"], $class->getName());
				}
			}
		}
		$this->createManyToMany();
	}

	protected function getTableName($classname){
		foreach ($this->classes as $table=>$class){
			if($class->getName()===$classname)
				return $table;
		}
		$posSlash=strrpos($classname, '\\');
		$tablename=substr($classname,  $posSlash+ 1);
		return lcfirst($tablename);
	}

	protected function createManyToMany(){
		foreach ($this->classes as $table=>$class){
			if($class->isAssociation()===true){
				$members=$class->getManyToOneMembers();
				if(sizeof($members)==2){
					$manyToOne1=$members[0]->getManyToOne();
					$manyToOne2=$members[1]->getManyToOne();
					$table1=$this->getTableName($manyToOne1->className);
					$table2=$this->getTableName($manyToOne2->className);
					$class1=$this->classes[$table1];
					$class2=$this->classes[$table2];
					$tableMember=\lcfirst($table)."s";
					$table1Member=\lcfirst($table1)."s";
					$table2Member=\lcfirst($table2)."s";
					$joinTable1=$this->getJoinTableArray($class1, $manyToOne1);
					$joinTable2=$this->getJoinTableArray($class2, $manyToOne2);
					$class1->addManyToMany($table2Member, $manyToOne2->className, $table1Member, $table,$joinTable1,$joinTable2);
					$class1->removeMember($tableMember);

					$class2->addManyToMany($table1Member, $manyToOne1->className, $table2Member, $table,$joinTable2,$joinTable1);
					$class2->removeMember($tableMember);
					unset($this->classes[$table]);
				}else{
					return;
				}
			}
		}
	}

	protected function getJoinTableArray(Model $class,JoinColumnAnnotation $joinColumn){
		$pk=$class->getPrimaryKey();
		$fk=$joinColumn->name;
		$dFk=$class->getDefaultFk();
		if($fk!==$dFk){
			if($pk!==null && $fk!==null)
				return ["name"=>$fk, "referencedColumnName"=>$pk->getName()];
		}
		return [];
	}

	abstract protected function getTablesName();

	abstract protected function getFieldsInfos($tableName);

	abstract protected function getPrimaryKeys($tableName);

	protected function writeFile($filename,$data){
		return file_put_contents($filename,$data);
	}
}

<?php

namespace Ubiquity\controllers\crud;

use Ubiquity\orm\OrmUtils;
use Ubiquity\utils\http\URequest;
use Ubiquity\orm\DAO;
use Ubiquity\orm\parser\Reflexion;
use Ubiquity\cache\database\DbCache;
use Ubiquity\db\SqlUtils;
use Ubiquity\utils\base\UString;

class CRUDHelper {
	public static function getIdentifierFunction($model) {
		$pks = self::getPks ( $model );
		return function ($index, $instance) use ($pks) {
			$values = [ ];
			foreach ( $pks as $pk ) {
				$getter = "get" . ucfirst ( $pk );
				if (method_exists ( $instance, $getter )) {
					$values [] = $instance->{$getter} ();
				}
			}
			return implode ( "_", $values );
		};
	}
	
	public static function search($model,$search,$fields,$initialCondition="1=1"){
		$words=preg_split("@(\s*?(\(|\)|\|\||\&\&)\s*?)@", $search);
		$words=array_filter($words,'strlen');
		$condition=$search;
		foreach ($words as $word){
			$word=trim($word);
			$condition=UString::replaceFirstOccurrence($word, "(".SqlUtils::getSearchWhere($fields,$word).")", $condition);
		}
		
		$condition=str_replace("||", " OR ", $condition);
		$condition=str_replace("&&", " AND ", $condition);
		$condition='('.$condition.') AND '.$initialCondition.'';
		return DAO::getAll($model,$condition);
	}
	
	public static function update($instance,$values,$updateManyToOneInForm=true,$updateManyToManyInForm=false) {
		$update=false;
		$className=\get_class($instance);
		$relations=OrmUtils::getManyToOneFields($className);
		$fieldTypes=OrmUtils::getFieldTypes($className);
		foreach ( $fieldTypes as $property => $type ) {
			if ($type == "tinyint(1)") {
				if (isset($values[$property])) {
					$values[$property]=1;
				} else {
					$values[$property]=0;
				}
			}
		}
		URequest::setValuesToObject($instance, $values);
		foreach ( $relations as $member ) {
			if ($updateManyToOneInForm) {
				$joinColumn=OrmUtils::getAnnotationInfoMember($className, "#joinColumn", $member);
				if ($joinColumn) {
					$fkClass=$joinColumn["className"];
					$fkField=$joinColumn["name"];
					if (isset($values[$fkField])) {
						$fkObject=DAO::getOne($fkClass, $values["$fkField"]);
						Reflexion::setMemberValue($instance, $member, $fkObject);
					}
				}
			}
		}
		if (isset($instance)) {
			if ($instance->_new) {
				$update=DAO::insert($instance);
			} else {
				$update=DAO::update($instance);
				if (DbCache::$active) {
					// TODO update dbCache
				}
			}
			if ($update) {
				if ($updateManyToManyInForm) {
					$relations=OrmUtils::getManyToManyFields($className);
					foreach ( $relations as $member ) {
						if (($annot=OrmUtils::getAnnotationInfoMember($className, "#manyToMany", $member)) !== false) {
							$newField=$member . "Ids";
							$fkClass=$annot["targetEntity"];
							$fkObjects=DAO::getAll($fkClass, self::getMultiWhere($values[$newField], $className));
							if (Reflexion::setMemberValue($instance, $member, $fkObjects)) {
								DAO::insertOrUpdateManyToMany($instance, $member);
							}
						}
					}
				}
			}
		}
		return $update;
	}
	
	private static function getPks($model) {
		$instance=new $model();
		return OrmUtils::getKeyFields($instance);
	}
	
	private static function getMultiWhere($ids, $class) {
		$pk=OrmUtils::getFirstKey($class);
		$ids=explode(",", $ids);
		if (sizeof($ids) < 1)
			return "";
		$strs=[ ];
		$idCount=\sizeof($ids);
		for($i=0; $i < $idCount; $i++) {
			$strs[]=$pk . "='" . $ids[$i] . "'";
		}
		return implode(" OR ", $strs);
	}
	
	public static function getFkIntance($instance,$model,$member){
		$result=[];
		if (($annot=OrmUtils::getAnnotationInfoMember($model, "#oneToMany", $member)) !== false) {
			$objectFK=DAO::getOneToMany($instance, $member);
			$fkClass=$annot["className"];
		} elseif (($annot=OrmUtils::getAnnotationInfoMember($model, "#manyToMany", $member)) !== false) {
			$objectFK=DAO::getManyToMany($instance, $member);
			$fkClass=$annot["targetEntity"];
		} else {
			$objectFK=Reflexion::getMemberValue($instance, $member);
			if (isset($objectFK))
				$fkClass=\get_class($objectFK);
		}
		if(isset($fkClass)){
			$fkTable=OrmUtils::getTableName($fkClass);
			$result[$member]=compact("objectFK","fkClass","fkTable");
		}
		return $result;
	}
	
	public static function getFKIntances($instance,$model){
		$result=[];
		$relations=OrmUtils::getFieldsInRelations($model);
		foreach ( $relations as $member ) {
			$fkInstance=self::getFkIntance($instance, $model, $member);
			if(sizeof($fkInstance)>0){
				$result=array_merge($result,$fkInstance);
			}
		}
		return $result;
	}
}


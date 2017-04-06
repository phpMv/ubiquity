<?php
namespace micro\orm;

/**
 * Utilitaires de mappage Objet/relationnel
 * @author jc
 * @version 1.0.0.4
 * @package orm
 */
class OrmUtils{
	public static $ormCache;
	private static $modelsMetadatas;

	public static function createOrmModelCache($className){
		$key=\str_replace("\\", DIRECTORY_SEPARATOR, $className);
		if(!self::$ormCache->exists($key)){
			$p=new ModelParser();
			$p->parse($className);
			self::$ormCache->store($key, $p->__toString());
		}
		self::$modelsMetadatas[$className]=self::$ormCache->fetch($key);
		return self::$modelsMetadatas[$className];
	}

	public static function getModelMetadata($className){
		if(!isset(self::$modelsMetadatas[$className])){
			self::createOrmModelCache($className);
		}
		return self::$modelsMetadatas[$className];
	}

	public static function isSerializable($class,$member){
		$ret=self::getAnnotationInfo($class,"#notSerializable");
		if ($ret!==false)
			return \array_search($member, $ret)===false;
		else
			return true;
	}

	public static function isNullable($class,$member){
		$ret=self::getAnnotationInfo($class,"#nullable");
		if ($ret!==false)
			return \array_search($member, $ret)!==false;
		else
			return false;
	}

	public static function getFieldName($class,$member){
		$ret=self::getAnnotationInfo($class, "#fieldNames");
		if($ret===false)
			$ret=$member;
		else
			$ret=$ret[$member];
		return $ret;
	}

	public static function getTableName($class){
		return self::getModelMetadata($class)["#tableName"];
	}

	public static function getKeyFieldsAndValues($instance){
		$kf=self::getAnnotationInfo(get_class($instance), "#primaryKeys");
		return self::getMembersAndValues($instance,$kf);
	}

	public static function getKeyFields($instance){
		return self::getAnnotationInfo(get_class($instance), "#primaryKeys");
	}

	public function getMembers($className){
		$fieldNames=self::getAnnotationInfo($className, "#fieldNames");
		if($fieldNames!==false)
			return \array_keys($fieldNames);
		return [];
	}

	public static function getMembersAndValues($instance,$members=NULL){
		$ret=array();
		$className=get_class($instance);
		if(is_null($members))
			$members=self::getMembers($className);
		foreach ($members as $member){
			if(OrmUtils::isSerializable($className,$member)){
				$v=Reflexion::getMemberValue($instance, $member);
				if(($v!==null && $v!=="") || (($v===null || $v==="") && OrmUtils::isNullable($className, $member))){
					$name=self::getFieldName($className, $member);
					$ret[$name]=$v;
				}
			}
		}
		return $ret;
	}

	public static function getFirstKey($class){
		$kf=self::getAnnotationInfo($class, "#primaryKeys");
		return \reset($kf);
	}

	public static function getFirstKeyValue($instance){
		$fkv=self::getKeyFieldsAndValues($instance);
		return \reset($fkv);
	}

	/**
	 * @param object $instance
	 * @return mixed[]
	 */
	public static function getManyToOneMembersAndValues($instance){
		$ret=array();
		$class=get_class($instance);
		$members=self::getAnnotationInfo($class, "#manyToOne");
		if($members!==false){
			foreach ($members as $member){
				$memberAccessor="get".ucfirst($member);
				if(method_exists($instance,$memberAccessor)){
					$memberInstance=$instance->$memberAccessor();
					if(isset($memberInstance)){
						$keyValues=self::getKeyFieldsAndValues($memberInstance);
						if(sizeof($keyValues)>0){
							$fkName=self::getJoinColumnName($class, $member);
							$ret[$fkName]=reset($keyValues);
						}
					}
				}
			}
		}
		return $ret;
	}

	public static function getMembersWithAnnotation($class,$annotation){
		if(isset(self::getModelMetadata($class)[$annotation]))
			return self::getModelMetadata($class)[$annotation];
		return [];
	}



	/**
	 * @param object $instance
	 * @param string $memberKey
	 * @param array $array
	 * @return boolean
	 */
	public static function exists($instance,$memberKey,$array){
		$accessor="get".ucfirst($memberKey);
		if(method_exists($instance, $accessor)){
			if($array!==null){
				foreach ($array as $value){
					if($value->$accessor()==$instance->$accessor())
						return true;
				}
			}
		}
		return false;
	}

	public static function getJoinColumnName($class,$member){
		$annot=self::getAnnotationInfoMember($class, "#joinColumn",$member);
		if($annot!==false){
			$fkName=$annot["name"];
		}else{
			$fkName="id".ucfirst(self::getTableName(ucfirst($member)));
		}
		return $fkName;
	}

	public static function getAnnotationInfo($class,$keyAnnotation){
		if(isset(self::getModelMetadata($class)[$keyAnnotation]))
			return self::getModelMetadata($class)[$keyAnnotation];
		return false;
	}

	public static function getAnnotationInfoMember($class,$keyAnnotation,$member){
		$info=self::getAnnotationInfo($class, $keyAnnotation);
		if($info!==false){
			if(isset($info[$member])){
				return $info[$member];
			}
		}
		return false;
	}

	public static function getSerializableFields($class){
		$notSerializable=self::getAnnotationInfo($class, "#notSerializable");
		$fieldNames=\array_keys(self::getAnnotationInfo($class, "#fieldNames"));
		return \array_diff($fieldNames, $notSerializable);
	}

	public static function getFieldsInRelations($class){
		$result=[];
		if($manyToOne=self::getAnnotationInfo($class, "#manyToOne")){
			$result=\array_merge($result,$manyToOne);
		}
		if($oneToMany=self::getAnnotationInfo($class, "#oneToMany")){
			$result=\array_merge($result,\array_keys($oneToMany));
		}
		return $result;
	}
}

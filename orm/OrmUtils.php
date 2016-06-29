<?php
namespace micro\orm;

/**
 * Utilitaires de mappage Objet/relationnel
 * @author jc
 * @version 1.0.0.2
 * @package orm
 */
class OrmUtils{
	public static function isSerializable($class,$member){
		if (Reflexion::getAnnotationMember($class,$member,"Transient")!==FALSE || Reflexion::getAnnotationMember($class,$member,"ManyToOne")!==FALSE ||
		Reflexion::getAnnotationMember($class,$member,"ManyToMany")!==FALSE || Reflexion::getAnnotationMember($class,$member,"OneToMany")!==FALSE)
			return false;
		else
			return true;
	}

	public static function isNullable($class,$member){
		$ret=Reflexion::getAnnotationMember($class,$member,"Column");
		if (!$ret)
			return false;
		else
			return $ret->nullable;
	}

	public static function getFieldName($class,$member){
		$ret=Reflexion::getAnnotationMember($class, $member, "Column");
		if($ret==false)
			$ret=$member;
		else
			$ret=$ret->name;
		return $ret;
	}

	public static function getTableName($class){
		$ret=Reflexion::getAnnotationClass($class, "Table");
		if($ret==false)
			$ret=$class;
		else
			$ret=$ret->name;
		return $ret;
	}

	public static function getKeyFieldsAndValues($instance){
		$kf=Reflexion::getMembersWithAnnotation(get_class($instance), "Id");
		return Reflexion::getPropertiesAndValues($instance,$kf);
	}

	public static function getFirstKey($class){
		$kf=Reflexion::getMembersWithAnnotation($class, "Id");
		if(sizeof($kf)>0)
			return $kf[0]->getName();
	}

	public static function getFirstKeyValue($instance){
		$fkv=OrmUtils::getKeyFieldsAndValues($instance);
		return reset($fkv);
	}

	public static function getManyToOneMembersAndValues($instance){
		$ret=array();
		$class=get_class($instance);
		$members=Reflexion::getMembersWithAnnotation($class, "ManyToOne");
		foreach ($members as $member){
			$annot=OrmUtils::getJoinColumn($class, $member->getName());
			$memberAccessor="get".ucfirst($member->getName());
			if(method_exists($instance,$memberAccessor)){
				$memberInstance=$instance->$memberAccessor();
				if(isset($memberInstance)){
					$keyValues=OrmUtils::getKeyFieldsAndValues($memberInstance);
					if(sizeof($keyValues)>0)
						$ret[$annot->name]=reset($keyValues);
				}
			}
		}
		return $ret;
	}

	public static function exists($instance,$memberKey,$array){
		$accessor="get".ucfirst($memberKey);
		if(method_exists($instance, $accessor)){
			if($array!=null){
				foreach ($array as $value){
					if($value->$accessor()==$instance->$accessor())
						return true;
				}
			}
		}
		return false;
	}

	public static function getJoinColumn($class,$member){
		$annot=Reflexion::getAnnotationMember($class, $member, "JoinColumn");
		if($annot==false){
			$annot=new \JoinColumn();
			$annot->name="id".ucfirst(OrmUtils::getTableName(ucfirst($member)));
		}
		return $annot;
	}

	public static function isMemberInManyToOne($class,$array,$member){
		foreach ($array as $memberMTO){
			$annot=Reflexion::getAnnotationMember($class, $memberMTO->getName(), "JoinColumn");
			if($annot->name==$member)
				return true;
		}
		return false;
	}
}
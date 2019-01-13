<?php

namespace Ubiquity\orm\traits;

use Ubiquity\orm\OrmUtils;
use Ubiquity\log\Logger;
use Ubiquity\orm\parser\Reflexion;

trait DAORelationsAssignmentsTrait {
	protected static function setToMember($member, $instance, $value, $accessor) {
		$instance->$accessor($value);
		$instance->_rest[$member]=$value;
	}
	
	protected static function getAccessor($member, $instance,$part) {
		$accessor="set" . ucfirst($member);
		if (method_exists($instance, $accessor)) {
			return $accessor;
		}
		$class=get_class($instance);
		Logger::warn("DAO", "Missing modifier " . $accessor . " in " . $class,$part);
		return false;
	}
	
	protected static function _affectsRelationObjects($manyToOneQueries,$oneToManyQueries,$manyToManyParsers,$objects,$included,$useCache){
		if(\sizeof($manyToOneQueries)>0){
			self::_affectsObjectsFromArray($manyToOneQueries,$included, function($object,$member,$manyToOneObjects,$fkField,$accessor){
				self::affectsManyToOneFromArray($object,$member,$manyToOneObjects,$fkField,$accessor);
			},'getManyToOne');
		}
		if(\sizeof($oneToManyQueries)>0){
			self::_affectsObjectsFromArray($oneToManyQueries,$included, function($object,$member,$relationObjects,$fkField,$accessor,$class){
				self::affectsOneToManyFromArray($object,$member,$relationObjects,$fkField,$accessor,$class);
			},'getOneToMany');
		}
		if(\sizeof($manyToManyParsers)>0){
			self::_affectsManyToManyObjectsFromArray($manyToManyParsers, $objects,$included,$useCache);
		}
	}
	
	private static function affectsManyToOneFromArray($object,$member,$manyToOneObjects,$fkField,$accessor){
		if(isset($object->$fkField)){
			$value=$manyToOneObjects[$object->$fkField];
			self::setToMember($member, $object, $value, $accessor);
		}
	}
	
	/**
	 * @param object $instance
	 * @param string $member
	 * @param array $array
	 * @param string $mappedBy
	 * @param string $class
	 */
	private static function affectsOneToManyFromArray($instance, $member, $array=null, $mappedBy=null,$accessor="",$class="") {
		$ret=array ();
		if (!isset($mappedBy)){
			$annot=OrmUtils::getAnnotationInfoMember($class, "#oneToMany", $member);
			$mappedBy=$annot["mappedBy"];
		}
		if ($mappedBy !== false) {
			$fkv=OrmUtils::getFirstKeyValue($instance);
			self::_getOneToManyFromArray($ret, $array, $fkv, $mappedBy);
			self::setToMember($member, $instance, $ret, $accessor);
		}
		return $ret;
	}
	
	private static function _affectsObjectsFromArray($queries,$included,$affectsCallback,$part,$useCache=NULL){
		$includedNext=false;
		foreach ($queries as $key=>$pendingRelationsRequest){
			list($class,$member,$fkField)=\explode("|", $key);
			if(is_array($included)){
				$includedNext=self::_getIncludedNext($included, $member);
			}
			$objectsParsers=$pendingRelationsRequest->getObjectsConditionParsers();
			
			foreach ($objectsParsers as $objectsConditionParser){
				$objectsConditionParser->compileParts();
				$relationObjects=self::_getAll($class,$objectsConditionParser->getConditionParser(),$includedNext,$useCache);
				$objects=$objectsConditionParser->getObjects();
				if($accessor=self::getAccessor($member, current($objects), $part)){
					foreach ($objects as $object){
						$affectsCallback($object, $member,$relationObjects,$fkField,$accessor,$class);
					}
				}
			}
		}
	}
	
	private static function _affectsManyToManyObjectsFromArray($parsers,$objects,$included,$useCache=NULL){
		$includedNext=false;
		foreach ($parsers as $key=>$parser){
			list($class,$member)=\explode("|", $key);
			if(is_array($included)){
				$includedNext=self::_getIncludedNext($included, $member);
			}
			$myPkValues=[];
			$cParser=self::generateManyToManyParser($parser, $myPkValues);
			$relationObjects=self::_getAll($class,$cParser,$includedNext,$useCache);
			if($accessor=self::getAccessor($member, current($objects), 'getManyToMany')){
				$key=OrmUtils::getFirstKey($class);
				foreach ($objects as $object){
					$pkV=Reflexion::getMemberValue($object, $key);
					if(isset($myPkValues[$pkV])){
						$ret=self::getManyToManyFromArrayIds($relationObjects, $myPkValues[$pkV]);
						self::setToMember($member, $object, $ret, $accessor);
					}
				}
			}
		}
	}
}


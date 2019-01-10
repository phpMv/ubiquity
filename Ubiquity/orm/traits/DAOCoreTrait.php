<?php

namespace Ubiquity\orm\traits;

use Ubiquity\orm\OrmUtils;
use Ubiquity\log\Logger;
use Ubiquity\events\EventsManager;
use Ubiquity\orm\parser\ConditionParser;
use Ubiquity\db\SqlUtils;

/**
 * @author jc
 * @property \Ubiquity\db\Database $db
 *
 */
trait DAOCoreTrait {
	abstract protected static function _affectsRelationObjects($manyToOneQueries,$oneToManyQueries,$manyToManyParsers,$objects,$included,$useCache);
	abstract protected static function prepareManyToMany(&$ret,$instance, $member, $annot=null);
	abstract protected static function prepareManyToOne(&$ret, $instance,$value, $fkField,$annotationArray);
	abstract protected static function prepareOneToMany(&$ret,$instance, $member, $annot=null);
	abstract protected static function _initRelationFields($included,$metaDatas,&$invertedJoinColumns,&$oneToManyFields,&$manyToManyFields);
	abstract protected static function getIncludedForStep($included);
	
	private static function _getOneToManyFromArray(&$ret, $array, $fkv, $mappedBy) {
		$elementAccessor="get" . ucfirst($mappedBy);
		foreach ( $array as $element ) {
			$elementRef=$element->$elementAccessor();
			if (!is_null($elementRef)) {
				if(is_object($elementRef))
					$idElementRef=OrmUtils::getFirstKeyValue($elementRef);
					else
						$idElementRef=$elementRef;
						if ($idElementRef == $fkv)
							$ret[]=$element;
			}
		}
	}
	
	private static function setToMember($member, $instance, $value, $class, $part) {
		$accessor="set" . ucfirst($member);
		if (method_exists($instance, $accessor)) {
			Logger::info("DAO", "Affectation de " . $member . " pour l'objet " . $class,$part);
			$instance->$accessor($value);
			$instance->_rest[$member]=$value;
		} else {
			Logger::warn("DAO", "L'accesseur " . $accessor . " est manquant pour " . $class,$part);
		}
	}
	
	private static function getManyToManyFromArray($instance, $array, $class, $parser) {
		$ret=[];
		$continue=true;
		$accessorToMember="get" . ucfirst($parser->getInversedBy());
		$myPkAccessor="get" . ucfirst($parser->getMyPk());
		
		if (!method_exists($instance, $myPkAccessor)) {
			Logger::warn("DAO", "L'accesseur au membre clé primaire " . $myPkAccessor . " est manquant pour " . $class,"ManyToMany");
		}
		if (count($array) > 0){
			$continue=method_exists(reset($array), $accessorToMember);
		}
		if ($continue) {
			foreach ( $array as $targetEntityInstance ) {
				$instances=$targetEntityInstance->$accessorToMember();
				if (is_array($instances)) {
					foreach ( $instances as $inst ) {
						if ($inst->$myPkAccessor() == $instance->$myPkAccessor())
							array_push($ret, $targetEntityInstance);
					}
				}
			}
		} else {
			Logger::warn("DAO", "L'accesseur au membre " . $parser->getInversedBy() . " est manquant pour " . $parser->getTargetEntity(),"ManyToMany");
		}
		return $ret;
	}
	
	protected static function _getOne($className,ConditionParser $conditionParser,$included,$useCache){
		$conditionParser->limitOne();
		$retour=self::_getAll($className, $conditionParser, $included,$useCache);
		if (sizeof($retour) < 1){
			return null;
		}
		$result= \reset($retour);
		EventsManager::trigger("dao.getone", $result,$className);
		return $result;
	}
	
	
	/**
	 * @param string $className
	 * @param ConditionParser $conditionParser
	 * @param boolean|array $included
	 * @param boolean|null $useCache
	 * @return array
	 */
	protected static function _getAll($className, ConditionParser $conditionParser, $included=true,$useCache=NULL) {
		$included=self::getIncludedForStep($included);
		$objects=array ();
		$invertedJoinColumns=null;
		$oneToManyFields=null;
		$manyToManyFields=null;
		
		$metaDatas=OrmUtils::getModelMetadata($className);
		$tableName=$metaDatas["#tableName"];
		$hasIncluded=$included || (is_array($included) && sizeof($included)>0);
		if($hasIncluded){
			self::_initRelationFields($included, $metaDatas, $invertedJoinColumns, $oneToManyFields, $manyToManyFields);
		}
		$condition=SqlUtils::checkWhere($conditionParser->getCondition());
		$members=\array_diff($metaDatas["#fieldNames"],$metaDatas["#notSerializable"]);
		$query=self::$db->prepareAndExecute($tableName, $condition,$members,$conditionParser->getParams(),$useCache);
		$oneToManyQueries=[];
		$manyToOneQueries=[];
		$manyToManyParsers=[];
		
		foreach ( $query as $row ) {
			$object=self::loadObjectFromRow($row, $className, $invertedJoinColumns, $oneToManyFields,$manyToManyFields,$members, $oneToManyQueries,$manyToOneQueries,$manyToManyParsers);
			$key=OrmUtils::getKeyValues($object);
			$objects[$key]=$object;
		}
		if($hasIncluded){
			self::_affectsRelationObjects($manyToOneQueries, $oneToManyQueries, $manyToManyParsers, $objects, $included, $useCache);
		}
		EventsManager::trigger("dao.getall", $objects,$className);
		return $objects;
	}
	
	
	/**
	 * @param array $row
	 * @param string $className
	 * @param array $invertedJoinColumns
	 * @param array $oneToManyFields
	 * @param array $members
	 * @param array $oneToManyQueries
	 * @param array $manyToOneQueries
	 * @param array $manyToManyParsers
	 * @return object
	 */
	private static function loadObjectFromRow($row, $className, $invertedJoinColumns, $oneToManyFields, $manyToManyFields,$members,&$oneToManyQueries,&$manyToOneQueries,&$manyToManyParsers) {
		$o=new $className();
		foreach ( $row as $k => $v ) {
			if(sizeof($fields=\array_keys($members,$k))>0){
				foreach ($fields as $field){
					$accesseur="set" . ucfirst($field);
					if (method_exists($o, $accesseur)) {
						$o->$accesseur($v);
					}
				}
			}
			$o->_rest[$k]=$v;
			if (isset($invertedJoinColumns) && isset($invertedJoinColumns[$k])) {
				$fk="_".$k;
				$o->$fk=$v;
				self::prepareManyToOne($manyToOneQueries,$o,$v, $fk,$invertedJoinColumns[$k]);
			}
		}
		if (isset($oneToManyFields)) {
			foreach ( $oneToManyFields as $k => $annot ) {
				self::prepareOneToMany($oneToManyQueries,$o, $k, $annot);
			}
		}
		if (isset($manyToManyFields)) {
			foreach ( $manyToManyFields as $k => $annot ) {
				self::prepareManyToMany($manyToManyParsers,$o, $k, $annot);
			}
		}
		return $o;
	}
	
	private static function parseKey(&$keyValues,$className){
		if (!is_array($keyValues)) {
			if (strrpos($keyValues, "=") === false && strrpos($keyValues, ">") === false && strrpos($keyValues, "<") === false) {
				$keyValues="`" . OrmUtils::getFirstKey($className) . "`='" . $keyValues . "'";
			}
		}
	}
}


<?php

namespace Ubiquity\orm\traits;

use Ubiquity\orm\OrmUtils;
use Ubiquity\log\Logger;
use Ubiquity\events\EventsManager;
use Ubiquity\orm\parser\ConditionParser;
use Ubiquity\db\SqlUtils;
use Ubiquity\orm\parser\Reflexion;

/**
 * @author jc
 * @property \Ubiquity\db\Database $db
 *
 */
trait DAOCoreTrait {
	abstract protected static function _affectsRelationObjects($className,$classPropKey,$manyToOneQueries,$oneToManyQueries,$manyToManyParsers,$objects,$included,$useCache);
	abstract protected static function prepareManyToMany(&$ret,$instance, $member, $annot=null);
	abstract protected static function prepareManyToOne(&$ret, $instance,$value, $fkField,$annotationArray);
	abstract protected static function prepareOneToMany(&$ret,$instance, $member, $annot=null);
	abstract protected static function _initRelationFields($included,$metaDatas,&$invertedJoinColumns,&$oneToManyFields,&$manyToManyFields);
	abstract protected static function getIncludedForStep($included);
	
	private static function _getOneToManyFromArray(&$ret, $array, $fkv, $elementAccessor,$prop) {
		foreach ( $array as $element ) {
			$elementRef=$element->$elementAccessor();
			if (($elementRef == $fkv) || (is_object($elementRef) && Reflexion::getPropValue($elementRef,$prop) == $fkv)){
				$ret[]=$element;
			}
		}
	}
	
/*	private static function _getOneToManyFromArray($array, $fkv, $mappedBy,$prop) {
		$elementAccessor="get" . ucfirst($mappedBy);
		return array_filter($array,function($element) use($elementAccessor,$fkv,$prop){
			$elementRef=$element->$elementAccessor();
			return ($elementRef == $fkv) || (is_object($elementRef) && Reflexion::getPropValue($elementRef,$prop) == $fkv);
		});
	}
	*/
	private static function getManyToManyFromArray($instance, $array, $class, $parser) {
		$ret=[];
		$continue=true;
		$accessorToMember="get" . ucfirst($parser->getInversedBy());
		$myPkAccessor="get" . ucfirst($parser->getMyPk());
		
		if (!method_exists($instance, $myPkAccessor)) {
			Logger::warn("DAO", "L'accesseur au membre clé primaire " . $myPkAccessor . " est manquant pour " . $class,"ManyToMany");
		}
		if (sizeof($array) > 0){
			$continue=method_exists(current($array), $accessorToMember);
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
		$result= \current($retour);
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
		$propsKeys=OrmUtils::getPropKeys($className);
		$accessors=OrmUtils::getAccessors($className,$members);
		$fields=array_flip($members);
		foreach ( $query as $row ) {
			$object=self::loadObjectFromRow($row, $className, $invertedJoinColumns, $oneToManyFields,$manyToManyFields, $oneToManyQueries,$manyToOneQueries,$manyToManyParsers,$accessors,$fields);
			$key=OrmUtils::getPropKeyValues($object,$propsKeys);
			$objects[$key]=$object;
		}
		if($hasIncluded){
			$classPropKey=OrmUtils::getFirstPropKey($className);
			self::_affectsRelationObjects($className,$classPropKey,$manyToOneQueries, $oneToManyQueries, $manyToManyParsers, $objects, $included, $useCache);
		}
		EventsManager::trigger("dao.getall", $objects,$className);
		return $objects;
	}
	
	
	/**
	 * @param array $row
	 * @param string $className
	 * @param array $invertedJoinColumns
	 * @param array $oneToManyFields
	 * @param array $oneToManyQueries
	 * @param array $manyToOneQueries
	 * @param array $manyToManyParsers
	 * @param array $accessors
	 * @param array $fields
	 * @return object
	 */
	private static function loadObjectFromRow($row, $className, &$invertedJoinColumns, &$oneToManyFields, &$manyToManyFields,&$oneToManyQueries,&$manyToOneQueries,&$manyToManyParsers,&$accessors,&$fields) {
		$o=new $className();
		foreach ( $row as $k => $v ) {
			if(isset($fields[$k])){
				if(isset($accessors[$k])){
					$accesseur=$accessors[$k];
					$o->$accesseur($v);
				}elseif(isset($accessors[$fields[$k]])){
					$accesseur=$accessors[$fields[$k]];
					$o->$accesseur($v);
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


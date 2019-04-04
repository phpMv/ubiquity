<?php

namespace Ubiquity\orm\traits;

use Ubiquity\db\SqlUtils;
use Ubiquity\events\DAOEvents;
use Ubiquity\events\EventsManager;
use Ubiquity\log\Logger;
use Ubiquity\orm\OrmUtils;
use Ubiquity\orm\parser\ConditionParser;
use Ubiquity\orm\parser\Reflexion;

/**
 * Core Trait for DAO class.
 * Ubiquity\orm\traits$DAOCoreTrait
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.2
 *
 * @property \Ubiquity\db\Database $db
 *
 */
trait DAOCoreTrait {
	protected static $accessors=[];
	abstract protected static function _affectsRelationObjects($className, $classPropKey, $manyToOneQueries, $oneToManyQueries, $manyToManyParsers, $objects, $included, $useCache);
	
	abstract protected static function prepareManyToMany(&$ret, $instance, $member, $annot = null);
	
	abstract protected static function prepareManyToOne(&$ret, $instance, $value, $fkField, $annotationArray);
	
	abstract protected static function prepareOneToMany(&$ret, $instance, $member, $annot = null);
	
	abstract protected static function _initRelationFields($included, $metaDatas, &$invertedJoinColumns, &$oneToManyFields, &$manyToManyFields);
	
	abstract protected static function getIncludedForStep($included);
	
	private static function _getOneToManyFromArray(&$ret, $array, $fkv, $elementAccessor, $prop) {
		foreach ( $array as $element ) {
			$elementRef = $element->$elementAccessor ();
			if (($elementRef == $fkv) || (is_object ( $elementRef ) && Reflexion::getPropValue ( $elementRef, $prop ) == $fkv)) {
				$ret [] = $element;
			}
		}
	}
	
	/*
	 * private static function _getOneToManyFromArray($array, $fkv, $mappedBy,$prop) {
	 * $elementAccessor="get" . ucfirst($mappedBy);
	 * return array_filter($array,function($element) use($elementAccessor,$fkv,$prop){
	 * $elementRef=$element->$elementAccessor();
	 * return ($elementRef == $fkv) || (is_object($elementRef) && Reflexion::getPropValue($elementRef,$prop) == $fkv);
	 * });
	 * }
	 */
	private static function getManyToManyFromArray($instance, $array, $class, $parser) {
		$ret = [ ];
		$continue = true;
		$accessorToMember = "get" . ucfirst ( $parser->getInversedBy () );
		$myPkAccessor = "get" . ucfirst ( $parser->getMyPk () );
		$pk = self::getFirstKeyValue_ ( $instance );
		
		if (sizeof ( $array ) > 0) {
			$continue = method_exists ( current ( $array ), $accessorToMember );
		}
		if ($continue) {
			foreach ( $array as $targetEntityInstance ) {
				$instances = $targetEntityInstance->$accessorToMember ();
				if (is_array ( $instances )) {
					foreach ( $instances as $inst ) {
						if ($inst->$myPkAccessor () == $pk)
							array_push ( $ret, $targetEntityInstance );
					}
				}
			}
		} else {
			Logger::warn ( "DAO", "L'accesseur au membre " . $parser->getInversedBy () . " est manquant pour " . $parser->getTargetEntity (), "ManyToMany" );
		}
		return $ret;
	}
	
	protected static function getClass_($instance) {
		if (is_object ( $instance )) {
			return get_class ( $instance );
		}
		return $instance [0];
	}
	
	protected static function getInstance_($instance) {
		if (is_object ( $instance )) {
			return $instance;
		}
		return $instance [0];
	}
	
	protected static function getValue_($instance, $member) {
		if (is_object ( $instance )) {
			return Reflexion::getMemberValue ( $instance, $member );
		}
		return $instance [1];
	}
	
	protected static function getFirstKeyValue_($instance) {
		if (is_object ( $instance )) {
			return OrmUtils::getFirstKeyValue ( $instance );
		}
		return $instance [1];
	}
	
	protected static function _getOne($className, ConditionParser $conditionParser, $included, $useCache) {
		$conditionParser->limitOne ();
		$retour = self::_getAll ( $className, $conditionParser, $included, $useCache );
		if (sizeof ( $retour ) < 1) {
			return null;
		}
		$result = \current ( $retour );
		EventsManager::trigger ( DAOEvents::GET_ONE, $result, $className );
		return $result;
	}
	
	/**
	 *
	 * @param string $className
	 * @param ConditionParser $conditionParser
	 * @param boolean|array $included
	 * @param boolean|null $useCache
	 * @return array
	 */
	protected static function _getAll($className, ConditionParser $conditionParser, $included = true, $useCache = NULL) {
		$included = self::getIncludedForStep ( $included );
		$objects = array ();
		$invertedJoinColumns = null;
		$oneToManyFields = null;
		$manyToManyFields = null;
		
		$metaDatas = OrmUtils::getModelMetadata ( $className );
		$tableName = $metaDatas ["#tableName"];
		$hasIncluded = $included || (is_array ( $included ) && sizeof ( $included ) > 0);
		if ($hasIncluded) {
			self::_initRelationFields ( $included, $metaDatas, $invertedJoinColumns, $oneToManyFields, $manyToManyFields );
		}
		$condition = SqlUtils::checkWhere ( $conditionParser->getCondition () );
		$members = \array_diff ( $metaDatas ["#fieldNames"], $metaDatas ["#notSerializable"] );
		$transformers=$metaDatas ["#transformers"];
		$query = self::$db->prepareAndExecute ( $tableName, $condition, $members, $conditionParser->getParams (), $useCache );
		$oneToManyQueries = [ ];
		$manyToOneQueries = [ ];
		$manyToManyParsers = [ ];
		$propsKeys = OrmUtils::getPropKeys ( $className );
		$accessors = OrmUtils::getAccessors ( $className, $members );
		$fields = array_flip ( $members );
		$hasTransformers=sizeof($transformers)>0;
		if($row=current($query)){
			if($hasTransformers){
				$accessors=self::prepareTransformers($className,$accessors, $fields, $transformers,$row);
			}else{
				$accessors=self::prepareAccessors($className,$accessors, $fields,$row);
			}
		}
		if($hasTransformers){
			foreach ( $query as $row ) {
				$object = self::loadObjectFromRowTransformer($row, $className, $invertedJoinColumns, $manyToOneQueries, $accessors);
				self::postLoadObject($object, $oneToManyFields, $manyToManyFields, $oneToManyQueries, $manyToManyParsers);
				$key = OrmUtils::getPropKeyValues ( $object, $propsKeys );
				$objects [$key] = $object;
			}
		}else{
			foreach ( $query as $row ) {
				$object = self::loadObjectFromRow($row, $className, $invertedJoinColumns, $manyToOneQueries, $accessors);
				self::postLoadObject($object, $oneToManyFields, $manyToManyFields, $oneToManyQueries, $manyToManyParsers);
				$key = OrmUtils::getPropKeyValues ( $object, $propsKeys );
				$objects [$key] = $object;
			}
		}
		if ($hasIncluded) {
			$classPropKey = OrmUtils::getFirstPropKey ( $className );
			self::_affectsRelationObjects ( $className, $classPropKey, $manyToOneQueries, $oneToManyQueries, $manyToManyParsers, $objects, $included, $useCache );
		}
		EventsManager::trigger ( DAOEvents::GET_ALL, $objects, $className );
		return $objects;
	}
	
	private static function prepareTransformers($classname,&$accessors,&$fields,&$transformers,&$row){
		if(isset(self::$accessors[$classname])){
			return self::$accessors[$classname];
		}
		$accesseurs=[];
		$transform='transform';
		foreach ( $row as $k => $vNotUsed ) {
			if (isset ( $accessors [$k] )) {
				$a = $accessors [$k];
			} elseif (isset ( $accessors [$fields [$k]??-1] )) {
				$a= $accessors [$fields [$k]];
			}
			if(isset($transformers[$k])){
				$trans=$transformers[$k];
				$accesseurs[$k]=function($o,$v) use($a,$trans,$transform){
					$o->$a($trans::$transform($v));
				};
			}else{
				$accesseurs[$k]=function($o,$v) use($a){
					$o->$a($v);
				};
			}
		}
		return self::$accessors[$classname]=$accesseurs;
	}
	
	private static function prepareAccessors($classname,&$accessors,&$fields,&$row){
		if(isset(self::$accessors[$classname])){
			return self::$accessors[$classname];
		}
		$accesseurs=[];
		foreach ( $row as $k => $vNotUsed ) {
			if (isset ( $accessors [$k] )) {
				$accesseurs[$k] = $accessors [$k];
			} elseif (isset ( $accessors [$fields [$k]??-1] )) {
				$accesseurs[$k]= $accessors [$fields [$k]];
			}
		}
		return self::$accessors[$classname]=$accesseurs;
	}
	
	/**
	 *
	 * @param array $row
	 * @param string $className
	 * @param array $invertedJoinColumns
	 * @param array $manyToOneQueries
	 * @param array $accessors
	 * @return object
	 */
	private static function loadObjectFromRow($row, $className, &$invertedJoinColumns, &$manyToOneQueries, &$accessors) {
		$o = new $className ();
		foreach ( $row as $k => $v ) {
			if (isset ( $accessors [$k] )) {
				$accesseur = $accessors [$k];
				$o->$accesseur ( $v );
			}
			$o->_rest [$k] = $v;
			if (isset ( $invertedJoinColumns ) && isset ( $invertedJoinColumns [$k] )) {
				$fk = "_" . $k;
				$o->$fk = $v;
				self::prepareManyToOne ( $manyToOneQueries, $o, $v, $fk, $invertedJoinColumns [$k] );
			}
		}
		return $o;
	}
	
	/**
	 *
	 * @param array $row
	 * @param string $className
	 * @param array $invertedJoinColumns
	 * @param array $manyToOneQueries
	 * @param array $accessors
	 * @return object
	 */
	private static function loadObjectFromRowTransformer($row, $className, &$invertedJoinColumns, &$manyToOneQueries, &$accessors) {
		$o = new $className ();
		foreach ( $row as $k => $v ) {
			if (isset ( $accessors [$k] )) {
				$accessors [$k]($o,$v);
			}
			$o->_rest [$k] = $v;
			if (isset ( $invertedJoinColumns ) && isset ( $invertedJoinColumns [$k] )) {
				$fk = "_" . $k;
				$o->$fk = $v;
				self::prepareManyToOne ( $manyToOneQueries, $o, $v, $fk, $invertedJoinColumns [$k] );
			}
		}
		return $o;
	}
	
	private static function postLoadObjectMember($o,$k,$v,&$invertedJoinColumns,&$manyToOneQueries){
		$o->_rest [$k] = $v;
		if (isset ( $invertedJoinColumns ) && isset ( $invertedJoinColumns [$k] )) {
			$fk = "_" . $k;
			$o->$fk = $v;
			self::prepareManyToOne ( $manyToOneQueries, $o, $v, $fk, $invertedJoinColumns [$k] );
		}
	}
	
	private static function postLoadObject($o,&$oneToManyFields,&$manyToManyFields,&$oneToManyQueries,&$manyToManyParsers){
		if (isset ( $oneToManyFields )) {
			foreach ( $oneToManyFields as $k => $annot ) {
				self::prepareOneToMany ( $oneToManyQueries, $o, $k, $annot );
			}
		}
		if (isset ( $manyToManyFields )) {
			foreach ( $manyToManyFields as $k => $annot ) {
				self::prepareManyToMany ( $manyToManyParsers, $o, $k, $annot );
			}
		}
	}
	
	private static function parseKey(&$keyValues, $className) {
		if (! is_array ( $keyValues )) {
			if (strrpos ( $keyValues, "=" ) === false && strrpos ( $keyValues, ">" ) === false && strrpos ( $keyValues, "<" ) === false) {
				$keyValues = "`" . OrmUtils::getFirstKey ( $className ) . "`='" . $keyValues . "'";
			}
		}
	}
}

<?php

namespace Ubiquity\orm;

use Ubiquity\db\Database;
use Ubiquity\log\Logger;
use Ubiquity\orm\parser\ManyToManyParser;
use Ubiquity\db\SqlUtils;
use Ubiquity\orm\parser\Reflexion;
use Ubiquity\orm\traits\DAOUpdatesTrait;
use Ubiquity\orm\traits\DAORelationsTrait;
use Ubiquity\orm\parser\ConditionParser;
use Ubiquity\orm\traits\DAOUQueries;

/**
 * Gateway class between database and object model
 * @author jc
 * @version 1.1.0.0
 * @package orm
 */
class DAO {
	use DAOUpdatesTrait,DAORelationsTrait,DAOUQueries;
	
	
	/**
	 * @var Database
	 */
	public static $db;

	/**
	 * Loads member associated with $instance by a ManyToOne relationship
	 * @param object $instance
	 * @param string $member
	 * @param boolean|array $included if true, loads associate members with associations, if array, example : ["client.*","commands"]
	 * @param boolean $useCache
	 */
	public static function getManyToOne($instance, $member, $included=false,$useCache=NULL) {
		$fieldAnnot=OrmUtils::getMemberJoinColumns($instance, $member);
		if($fieldAnnot!==null){
			$annotationArray=$fieldAnnot[1];
			$member=$annotationArray["member"];
			$value=Reflexion::getMemberValue($instance, $member);
			$key=OrmUtils::getFirstKey($annotationArray["className"]);
			$kv=array ($key => $value );
			$obj=self::getOne($annotationArray["className"], $kv, $included, $useCache);
			if ($obj !== null) {
				Logger::info("DAO", "Loading the member " . $member . " for the object " . \get_class($instance),"getManyToOne");
				$accesseur="set" . ucfirst($member);
				if (method_exists($instance, $accesseur)) {
					$instance->$accesseur($obj);
					$instance->_rest[$member]=$obj->_rest;
					return $obj;
				}
			}
		}
	}

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

	/**
	 * Assign / load the child records in the $member member of $instance.
	 * @param object $instance
	 * @param string $member Member on which a oneToMany annotation must be present
	 * @param boolean|array $included if true, loads associate members with associations, if array, example : ["client.*","commands"]
	 * @param boolean $useCache
	 * @param array $annot used internally
	 */
	public static function getOneToMany($instance, $member, $included=true,$useCache=NULL, $annot=null) {
		$ret=array ();
		$class=get_class($instance);
		if (!isset($annot))
			$annot=OrmUtils::getAnnotationInfoMember($class, "#oneToMany", $member);
			if ($annot !== false) {
				$fkAnnot=OrmUtils::getAnnotationInfoMember($annot["className"], "#joinColumn", $annot["mappedBy"]);
				if ($fkAnnot !== false) {
					$fkv=OrmUtils::getFirstKeyValue($instance);
					$ret=self::_getAll($annot["className"], ConditionParser::simple($fkAnnot["name"] . "= ?",$fkv), $included, $useCache);
					self::setToMember($member, $instance, $ret, $class, "getOneToMany");
				}
			}
			return $ret;
	}

	/**
	 * @param object $instance
	 * @param string $member
	 * @param array $array
	 * @param string $mappedBy
	 */
	public static function affectsOneToManyFromArray($instance, $member, $array=null, $mappedBy=null) {
		$ret=array ();
		$class=get_class($instance);
		if (!isset($mappedBy)){
			$annot=OrmUtils::getAnnotationInfoMember($class, "#oneToMany", $member);
			$mappedBy=$annot["mappedBy"];
		}
		if ($mappedBy !== false) {
				$fkv=OrmUtils::getFirstKeyValue($instance);
				self::_getOneToManyFromArray($ret, $array, $fkv, $mappedBy);
				self::setToMember($member, $instance, $ret, $class, "getOneToMany");
		}
		return $ret;
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

	/**
	 * Assigns / loads the child records in the $member member of $instance.
	 * If $ array is null, the records are loaded from the database
	 * @param object $instance
	 * @param string $member Member on which a ManyToMany annotation must be present
	 * @param boolean|array $included if true, loads associate members with associations, if array, example : ["client.*","commands"]
	 * @param array $array optional parameter containing the list of possible child records
	 * @param boolean $useCache
	 */
	public static function getManyToMany($instance, $member,$included=false,$array=null,$useCache=NULL){
		$ret=[];
		$class=get_class($instance);
		$parser=new ManyToManyParser($instance, $member);
		if ($parser->init()) {
			if (is_null($array)) {
				$accessor="get" . ucfirst($parser->getMyPk());
				$condition=" INNER JOIN `" . $parser->getJoinTable() . "` on `".$parser->getJoinTable()."`.`".$parser->getFkField()."`=`".$parser->getTargetEntityTable()."`.`".$parser->getPk()."` WHERE `".$parser->getJoinTable()."`.`". $parser->getMyFkField() . "`= ?";
				$ret=self::_getAll($parser->getTargetEntityClass(),ConditionParser::simple($condition, $instance->$accessor()),$included,$useCache);
			}else{
				$ret=self::getManyToManyFromArray($instance, $array, $class, $parser);
			}
			self::setToMember($member, $instance, $ret, $class, "getManyToMany");
		}
		return $ret;
	}

	/**
	 * @param object $instance
	 * @param array $array
	 * @param boolean $useCache
	 */
	public static function affectsManyToManys($instance,$array=NULL,$useCache=NULL){
		$metaDatas=OrmUtils::getModelMetadata(\get_class($instance));
		$manyToManyFields=$metaDatas["#manyToMany"];
		if(\sizeof($manyToManyFields)>0){
			foreach ($manyToManyFields as $member){
				self::getManyToMany($instance, $member,false,$array,$useCache);
			}
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

	/**
	 * Returns an array of $className objects from the database
	 * @param string $className class name of the model to load
	 * @param string $condition Part following the WHERE of an SQL statement
	 * @param boolean|array $included if true, loads associate members with associations, if array, example : ["client.*","commands"]
	 * @param array|null $parameters
	 * @param boolean $useCache use the active cache if true
	 * @return array
	 */
	public static function getAll($className, $condition='', $included=true,$parameters=null,$useCache=NULL) {
		return self::_getAll($className, new ConditionParser($condition,null,$parameters),$included,$useCache);
	}
	
	protected static function _getOne($className,ConditionParser $conditionParser,$included,$useCache){
		$conditionParser->limitOne();
		$retour=self::_getAll($className, $conditionParser, $included,$useCache);
		if (sizeof($retour) < 1){
			return null;
		}
		return \reset($retour);
	}
	

	
	/**
	 * @param string $className
	 * @param ConditionParser $conditionParser
	 * @param boolean|array $included
	 * @param boolean $useCache
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
		return $objects;
	}
	
	public static function paginate($className,$page=1,$rowsPerPage=20,$condition=null,$included=true){
		if(!isset($condition)){
			$condition="1=1";
		}
		return self::getAll($className,$condition." LIMIT ".$rowsPerPage." OFFSET ".(($page-1)*$rowsPerPage),$included);
	}
	
	public static function getRownum($className,$ids){
		$tableName=OrmUtils::getTableName($className);
		self::parseKey($ids,$className);
		$condition=SqlUtils::getCondition($ids,$className);
		$keys=implode(",", OrmUtils::getKeyFields($className));
		return self::$db->queryColumn("SELECT num FROM (SELECT *, @rownum:=@rownum + 1 AS num FROM `{$tableName}`, (SELECT @rownum:=0) r ORDER BY {$keys}) d WHERE ".$condition);
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

	/**
	 * Returns the number of objects of $className from the database respecting the condition possibly passed as parameter
	 * @param string $className complete classname of the model to load
	 * @param string $condition Part following the WHERE of an SQL statement
	 * @param array|null $parameters The query parameters
	 * @return int count of objects
	 */
	public static function count($className, $condition='',$parameters=null) {
		$tableName=OrmUtils::getTableName($className);
		if ($condition != '')
			$condition=" WHERE " . $condition;
		return self::$db->prepareAndFetchColumn("SELECT COUNT(*) FROM `" . $tableName ."`". $condition,$parameters);
	}

	/**
	 * Returns an instance of $className from the database, from $keyvalues values of the primary key
	 * @param String $className complete classname of the model to load
	 * @param Array|string $keyValues primary key values or condition
	 * @param boolean|array $included if true, charges associate members with association
	 * @param array|null $parameters the request parameters
	 * @param boolean $useCache use cache if true
	 * @return object the instance loaded or null if not found
	 */
	public static function getOne($className, $keyValues, $included=true,$parameters=null,$useCache=NULL) {
		$conditionParser=new ConditionParser();
		if(!isset($parameters)){
			$conditionParser->addKeyValues($keyValues,$className);
		}else{
			$conditionParser->setCondition($keyValues);
			$conditionParser->setParams($parameters);
		}
		return self::_getOne($className, $conditionParser, $included, $useCache);
	}
	
	private static function parseKey(&$keyValues,$className){
		if (!is_array($keyValues)) {
			if (strrpos($keyValues, "=") === false && strrpos($keyValues, ">") === false && strrpos($keyValues, "<") === false) {
				$keyValues="`" . OrmUtils::getFirstKey($className) . "`='" . $keyValues . "'";
			}
		}
	}

	/**
	 * Establishes the connection to the database using the past parameters
	 * @param string $dbType
	 * @param string $dbName
	 * @param string $serverName
	 * @param string $port
	 * @param string $user
	 * @param string $password
	 * @param array $options
	 * @param boolean $cache
	 */
	public static function connect($dbType,$dbName, $serverName="127.0.0.1", $port="3306", $user="root", $password="", $options=[],$cache=false) {
		self::$db=new Database($dbType,$dbName, $serverName, $port, $user, $password, $options,$cache);
		try {
			self::$db->connect();
		} catch (\Exception $e) {
			Logger::error("DAO", $e->getMessage());
			throw $e;
		}
	}

	/**
	 * Returns true if the connection to the database is established
	 * @return boolean
	 */
	public static function isConnected(){
		return self::$db!==null && (self::$db instanceof Database) && self::$db->isConnected();
	}
}

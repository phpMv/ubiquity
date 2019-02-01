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
use Ubiquity\orm\traits\DAOCoreTrait;
use Ubiquity\orm\traits\DAORelationsPrepareTrait;
use Ubiquity\exceptions\DAOException;
use Ubiquity\orm\traits\DAORelationsAssignmentsTrait;

/**
 * Gateway class between database and object model
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.1.5
 *
 */
class DAO {
	use DAOCoreTrait,DAOUpdatesTrait,DAORelationsTrait,DAORelationsPrepareTrait,DAORelationsAssignmentsTrait,DAOUQueries;

	/**
	 *
	 * @var Database
	 */
	public static $db;

	/**
	 * Loads member associated with $instance by a ManyToOne relationship
	 *
	 * @param object $instance
	 * @param string $member
	 * @param boolean|array $included
	 *        	if true, loads associate members with associations, if array, example : ["client.*","commands"]
	 * @param boolean|null $useCache
	 */
	public static function getManyToOne($instance, $member, $included = false, $useCache = NULL) {
		$fieldAnnot = OrmUtils::getMemberJoinColumns ( $instance, $member );
		if ($fieldAnnot !== null) {
			$annotationArray = $fieldAnnot [1];
			$member = $annotationArray ["member"];
			$value = Reflexion::getMemberValue ( $instance, $member );
			$key = OrmUtils::getFirstKey ( $annotationArray ["className"] );
			$kv = array ($key => $value );
			$obj = self::getOne ( $annotationArray ["className"], $kv, $included, null, $useCache );
			if ($obj !== null) {
				Logger::info ( "DAO", "Loading the member " . $member . " for the object " . \get_class ( $instance ), "getManyToOne" );
				$accesseur = "set" . ucfirst ( $member );
				if (method_exists ( $instance, $accesseur )) {
					$instance->$accesseur ( $obj );
					$instance->_rest [$member] = $obj->_rest;
					return $obj;
				}
			}
		}
	}

	/**
	 * Assign / load the child records in the $member member of $instance.
	 *
	 * @param object $instance
	 * @param string $member
	 *        	Member on which a oneToMany annotation must be present
	 * @param boolean|array $included
	 *        	if true, loads associate members with associations, if array, example : ["client.*","commands"]
	 * @param boolean $useCache
	 * @param array $annot
	 *        	used internally
	 */
	public static function getOneToMany($instance, $member, $included = true, $useCache = NULL, $annot = null) {
		$ret = array ();
		$class = get_class ( $instance );
		if (! isset ( $annot ))
			$annot = OrmUtils::getAnnotationInfoMember ( $class, "#oneToMany", $member );
		if ($annot !== false) {
			$fkAnnot = OrmUtils::getAnnotationInfoMember ( $annot ["className"], "#joinColumn", $annot ["mappedBy"] );
			if ($fkAnnot !== false) {
				$fkv = OrmUtils::getFirstKeyValue ( $instance );
				$ret = self::_getAll ( $annot ["className"], ConditionParser::simple ( $fkAnnot ["name"] . "= ?", $fkv ), $included, $useCache );
				if ($modifier = self::getAccessor ( $member, $instance, 'getOneToMany' )) {
					self::setToMember ( $member, $instance, $ret, $modifier );
				}
			}
		}
		return $ret;
	}

	/**
	 * Assigns / loads the child records in the $member member of $instance.
	 * If $array is null, the records are loaded from the database
	 *
	 * @param object $instance
	 * @param string $member
	 *        	Member on which a ManyToMany annotation must be present
	 * @param boolean|array $included
	 *        	if true, loads associate members with associations, if array, example : ["client.*","commands"]
	 * @param array $array
	 *        	optional parameter containing the list of possible child records
	 * @param boolean $useCache
	 */
	public static function getManyToMany($instance, $member, $included = false, $array = null, $useCache = NULL) {
		$ret = [ ];
		$class = get_class ( $instance );
		$parser = new ManyToManyParser ( $instance, $member );
		if ($parser->init ()) {
			if (is_null ( $array )) {
				$accessor = "get" . ucfirst ( $parser->getMyPk () );
				$condition = " INNER JOIN `" . $parser->getJoinTable () . "` on `" . $parser->getJoinTable () . "`.`" . $parser->getFkField () . "`=`" . $parser->getTargetEntityTable () . "`.`" . $parser->getPk () . "` WHERE `" . $parser->getJoinTable () . "`.`" . $parser->getMyFkField () . "`= ?";
				$ret = self::_getAll ( $parser->getTargetEntityClass (), ConditionParser::simple ( $condition, $instance->$accessor () ), $included, $useCache );
			} else {
				$ret = self::getManyToManyFromArray ( $instance, $array, $class, $parser );
			}
			if ($modifier = self::getAccessor ( $member, $instance, 'getManyToMany' )) {
				self::setToMember ( $member, $instance, $ret, $modifier );
			}
		}
		return $ret;
	}

	/**
	 *
	 * @param object $instance
	 * @param array $array
	 * @param boolean $useCache
	 */
	public static function affectsManyToManys($instance, $array = NULL, $useCache = NULL) {
		$metaDatas = OrmUtils::getModelMetadata ( \get_class ( $instance ) );
		$manyToManyFields = $metaDatas ["#manyToMany"];
		if (\sizeof ( $manyToManyFields ) > 0) {
			foreach ( $manyToManyFields as $member ) {
				self::getManyToMany ( $instance, $member, false, $array, $useCache );
			}
		}
	}

	/**
	 * Returns an array of $className objects from the database
	 *
	 * @param string $className
	 *        	class name of the model to load
	 * @param string $condition
	 *        	Part following the WHERE of an SQL statement
	 * @param boolean|array $included
	 *        	if true, loads associate members with associations, if array, example : ["client.*","commands"]
	 * @param array|null $parameters
	 * @param boolean $useCache
	 *        	use the active cache if true
	 * @return array
	 */
	public static function getAll($className, $condition = '', $included = true, $parameters = null, $useCache = NULL) {
		return self::_getAll ( $className, new ConditionParser ( $condition, null, $parameters ), $included, $useCache );
	}

	public static function paginate($className, $page = 1, $rowsPerPage = 20, $condition = null, $included = true) {
		if (! isset ( $condition )) {
			$condition = "1=1";
		}
		return self::getAll ( $className, $condition . " LIMIT " . $rowsPerPage . " OFFSET " . (($page - 1) * $rowsPerPage), $included );
	}

	public static function getRownum($className, $ids) {
		$tableName = OrmUtils::getTableName ( $className );
		self::parseKey ( $ids, $className );
		$condition = SqlUtils::getCondition ( $ids, $className );
		$keyFields = OrmUtils::getKeyFields ( $className );
		if (is_array ( $keyFields )) {
			$keys = implode ( ",", $keyFields );
		} else {
			$keys = "1";
		}
		return self::$db->queryColumn ( "SELECT num FROM (SELECT *, @rownum:=@rownum + 1 AS num FROM `{$tableName}`, (SELECT @rownum:=0) r ORDER BY {$keys}) d WHERE " . $condition );
	}

	/**
	 * Returns the number of objects of $className from the database respecting the condition possibly passed as parameter
	 *
	 * @param string $className
	 *        	complete classname of the model to load
	 * @param string $condition
	 *        	Part following the WHERE of an SQL statement
	 * @param array|null $parameters
	 *        	The query parameters
	 * @return int|false count of objects
	 */
	public static function count($className, $condition = '', $parameters = null) {
		$tableName = OrmUtils::getTableName ( $className );
		if ($condition != '')
			$condition = " WHERE " . $condition;
		return self::$db->prepareAndFetchColumn ( "SELECT COUNT(*) FROM `" . $tableName . "`" . $condition, $parameters );
	}

	/**
	 * Returns an instance of $className from the database, from $keyvalues values of the primary key
	 *
	 * @param String $className
	 *        	complete classname of the model to load
	 * @param Array|string $keyValues
	 *        	primary key values or condition
	 * @param boolean|array $included
	 *        	if true, charges associate members with association
	 * @param array|null $parameters
	 *        	the request parameters
	 * @param boolean|null $useCache
	 *        	use cache if true
	 * @return object the instance loaded or null if not found
	 */
	public static function getOne($className, $keyValues, $included = true, $parameters = null, $useCache = NULL) {
		$conditionParser = new ConditionParser ();
		if (! isset ( $parameters )) {
			$conditionParser->addKeyValues ( $keyValues, $className );
		} elseif (! is_array ( $keyValues )) {
			$conditionParser->setCondition ( $keyValues );
			$conditionParser->setParams ( $parameters );
		} else {
			throw new DAOException ( "The \$keyValues parameter should not be an array if \$parameters is not null" );
		}
		return self::_getOne ( $className, $conditionParser, $included, $useCache );
	}

	/**
	 * Establishes the connection to the database using the past parameters
	 *
	 * @param string $dbType
	 * @param string $dbName
	 * @param string $serverName
	 * @param string $port
	 * @param string $user
	 * @param string $password
	 * @param array $options
	 * @param boolean $cache
	 */
	public static function connect($dbType, $dbName, $serverName = '127.0.0.1', $port = '3306', $user = 'root', $password = '', $options = [], $cache = false) {
		self::$db = new Database ( $dbType, $dbName, $serverName, $port, $user, $password, $options, $cache );
		try {
			self::$db->connect ();
		} catch ( \Exception $e ) {
			Logger::error ( "DAO", $e->getMessage () );
			throw new DAOException ( $e->getMessage (), $e->getCode (), $e->getPrevious () );
		}
	}

	/**
	 * Establishes the connection to the database using the $config array
	 *
	 * @param array $config
	 *        	the config array (Startup::getConfig())
	 */
	public static function startDatabase(&$config) {
		$db = $config ['database'] ?? [ ];
		if ($db ['dbName'] !== '') {
			self::connect ( $db ['type'], $db ['dbName'], $db ['serverName'] ?? '127.0.0.1', $db ['port'] ?? 3306, $db ['user'] ?? 'root', $db ['password'] ?? '', $db ['options'] ?? [ ], $db ['cache'] ?? false);
		}
	}

	/**
	 * Returns true if the connection to the database is established
	 *
	 * @return boolean
	 */
	public static function isConnected() {
		return self::$db !== null && (self::$db instanceof Database) && self::$db->isConnected ();
	}
}

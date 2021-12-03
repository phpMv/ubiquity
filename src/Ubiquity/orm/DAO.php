<?php

namespace Ubiquity\orm;

use Ubiquity\db\Database;
use Ubiquity\log\Logger;
use Ubiquity\db\SqlUtils;
use Ubiquity\orm\traits\DAOUpdatesTrait;
use Ubiquity\orm\traits\DAORelationsTrait;
use Ubiquity\orm\parser\ConditionParser;
use Ubiquity\orm\traits\DAOUQueries;
use Ubiquity\orm\traits\DAOCoreTrait;
use Ubiquity\orm\traits\DAORelationsPrepareTrait;
use Ubiquity\exceptions\DAOException;
use Ubiquity\orm\traits\DAORelationsAssignmentsTrait;
use Ubiquity\orm\traits\DAOTransactionsTrait;
use Ubiquity\controllers\Startup;
use Ubiquity\cache\CacheManager;
use Ubiquity\orm\traits\DAOPooling;
use Ubiquity\orm\traits\DAOBulkUpdatesTrait;
use Ubiquity\orm\traits\DAOPreparedTrait;
use Ubiquity\cache\dao\AbstractDAOCache;

/**
 * Gateway class between database and object model.
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.2.6
 *
 */
class DAO {
	use DAOCoreTrait,DAOUpdatesTrait,DAORelationsTrait,DAORelationsPrepareTrait,DAORelationsAssignmentsTrait,
	DAOUQueries,DAOTransactionsTrait,DAOPooling,DAOBulkUpdatesTrait,DAOPreparedTrait;

	/**
	 *
	 * @var Database
	 */
	public static $db;
	public static $useTransformers = false;
	public static $transformerOp = 'transform';
	private static $conditionParsers = [ ];
	protected static $modelsDatabase = [ ];

	/**
	 *
	 * @var AbstractDAOCache
	 */
	protected static $cache;

	public static function getDb($model) {
		return self::getDatabase ( self::$modelsDatabase [$model] ?? 'default');
	}

	/**
	 * Returns an array of $className objects from the database
	 *
	 * @param string $className class name of the model to load
	 * @param string $condition Part following the WHERE of an SQL statement
	 * @param boolean|array $included if true, loads associate members with associations, if array, example : ['client.*','commands']
	 * @param array|null $parameters
	 * @param boolean $useCache use the active cache if true
	 * @return array
	 */
	public static function getAll($className, $condition = '', $included = true, $parameters = null, $useCache = NULL) {
		$db = self::getDb ( $className );
		return static::_getAll ( $db, $className, new ConditionParser ( $condition, null, $parameters ), $included, $useCache );
	}

	/**
	 * Returns an array of $className objects loaded by id from the database
	 *
	 * @param string $className class name of the model to load
	 * @param array $keyValues
	 * @param boolean|array $included if true, loads associate members with associations, if array, example : ['client.*','commands']
	 * @param string $condition additional condition
	 * @param boolean $useCache use the active cache if true
	 * @return array
	 */
	public static function getAllByIds($className, $keyValues = [ ], $included = true, $condition = '', $useCache = NULL) {
		$db = self::getDb ( $className );
		$key = OrmUtils::getFirstKey ( $className );
		$countK = \count ( $keyValues );
		if ($countK > 0) {
			$nCondition = $key . ' IN (' . \str_repeat ( '?,', $countK - 1 ) . '?)';
			if ($condition != null) {
				$nCondition .= ' AND ' . $condition;
			}
			return static::_getAll ( $db, $className, new ConditionParser ( $nCondition, null, $keyValues ), $included, $useCache );
		}
		return [];
		
	}

	public static function paginate($className, $page = 1, $rowsPerPage = 20, $condition = null, $included = true) {
		return self::getAll ( $className, ($condition ?? '1=1') . ' LIMIT ' . $rowsPerPage . ' OFFSET ' . (($page - 1) * $rowsPerPage), $included );
	}

	public static function getRownum($className, $ids) {
		$tableName = OrmUtils::getTableName ( $className );
		$db = self::getDb ( $className );
		$quote = $db->quote;
		self::parseKey ( $ids, $className, $quote );
		$condition = SqlUtils::getCondition ( $ids, $className );
		$keyFields = OrmUtils::getKeyFields ( $className );
		if (\is_array ( $keyFields )) {
			$keys = \implode ( ',', $keyFields );
		} else {
			$keys = '1';
		}
		return $db->getRowNum ( $tableName, $keys, $condition );
	}

	/**
	 * Returns the number of objects of $className from the database respecting the condition possibly passed as parameter
	 *
	 * @param string $className complete classname of the model to load
	 * @param string $condition Part following the WHERE of an SQL statement
	 * @param array|null $parameters The query parameters
	 * @return int|false count of objects
	 */
	public static function count($className, $condition = '', $parameters = null) {
		$tableName = OrmUtils::getTableName ( $className );
		if ($condition != '') {
			$condition = SqlUtils::checkWhere($condition);
		}
		$db = self::getDb ( $className );
		$quote = $db->quote;
		return $db->prepareAndFetchColumn ( 'SELECT COUNT(*) FROM ' . $quote . $tableName . $quote . $condition, $parameters );
	}

	/**
	 * Tests the existence of objects of $className from the database respecting the condition possibly passed as parameter
	 *
	 * @param string $className complete classname of the model to load
	 * @param string $condition Part following the WHERE of an SQL statement
	 * @param array|null $parameters The query parameters
	 * @return boolean
	 */
	public static function exists($className, $condition = '', $parameters = null) {
		$tableName = OrmUtils::getTableName ( $className );
		if ($condition != '') {
			$condition = SqlUtils::checkWhere($condition);
		}
		$db = self::getDb ( $className );
		$quote = $db->quote;
		return (1 == $db->prepareAndFetchColumn ( "SELECT EXISTS(SELECT 1 FROM {$quote}{$tableName}{$quote}{$condition})", $parameters ));
	}

	/**
	 * Returns an instance of $className from the database, from $keyvalues values of the primary key or with a condition
	 *
	 * @param String $className complete classname of the model to load
	 * @param array|string $condition condition or primary key values
	 * @param boolean|array $included if true, charges associate members with association
	 * @param array|null $parameters the request parameters
	 * @param boolean|null $useCache use cache if true
	 * @return object the instance loaded or null if not found
	 */
	public static function getOne($className, $condition, $included = true, $parameters = null, $useCache = NULL) {
		$db = self::getDb ( $className );
		$conditionParser = new ConditionParser ();
		if (! isset ( $parameters )) {
			$conditionParser->addKeyValues ( $condition, $className );
		} elseif (! is_array ( $condition )) {
			$conditionParser->setCondition ( $condition );
			$conditionParser->setParams ( $parameters );
		} else {
			throw new DAOException ( "The \$condition parameter should not be an array if \$parameters is not null" );
		}
		return static::_getOne ( $db, $className, $conditionParser, $included, $useCache );
	}

	/**
	 * Returns an instance of $className from the database, from $keyvalues values of the primary key
	 *
	 * @param String $className complete classname of the model to load
	 * @param array|string $keyValues primary key values or condition
	 * @param boolean|array $included if true, charges associate members with association
	 * @param boolean|null $useCache use cache if true
	 * @return object the instance loaded or null if not found
	 */
	public static function getById($className, $keyValues, $included = true, $useCache = NULL) {
		return static::_getOne ( self::getDatabase ( self::$modelsDatabase [$className] ?? 'default'), $className, self::getConditionParser ( $className, $keyValues ), $included, $useCache );
	}

	protected static function getConditionParser($className, $keyValues): ConditionParser {
		if (! isset ( self::$conditionParsers [$className] )) {
			$conditionParser = new ConditionParser ();
			$conditionParser->addKeyValues ( $keyValues, $className );
			self::$conditionParsers [$className] = $conditionParser;
		} else {
			self::$conditionParsers [$className]->setKeyValues ( $keyValues );
		}
		return self::$conditionParsers [$className];
	}

	/**
	 * Establishes the connection to the database using the past parameters
	 *
	 * @param string $offset
	 * @param string $wrapper
	 * @param string $dbType
	 * @param string $dbName
	 * @param string $serverName
	 * @param string $port
	 * @param string $user
	 * @param string $password
	 * @param array $options
	 * @param boolean $cache
	 */
	public static function connect($offset, $wrapper, $dbType, $dbName, $serverName = '127.0.0.1', $port = '3306', $user = 'root', $password = '', $options = [ ], $cache = false) {
		self::$db [$offset] = new Database ( $wrapper, $dbType, $dbName, $serverName, $port, $user, $password, $options, $cache, self::$pool );
		try {
			self::$db [$offset]->connect ();
		} catch ( \Exception $e ) {
			Logger::error ( "DAO", $e->getMessage () );
			throw new DAOException ( $e->getMessage (), $e->getCode (), $e->getPrevious () );
		}
	}

	/**
	 * Establishes the connection to the database using the $config array
	 *
	 * @param array $config the config array (Startup::getConfig())
	 */
	public static function startDatabase(&$config, $offset = null) {
		$db = $offset ? ($config ['database'] [$offset] ?? ($config ['database'] ?? [ ])) : ($config ['database'] ['default'] ?? $config ['database']);
		if ($db ['dbName'] !== '') {
			self::connect ( $offset ?? 'default', $db ['wrapper'] ?? \Ubiquity\db\providers\pdo\PDOWrapper::class, $db ['type'], $db ['dbName'], $db ['serverName'] ?? '127.0.0.1', $db ['port'] ?? 3306, $db ['user'] ?? 'root', $db ['password'] ?? '', $db ['options'] ?? [ ], $db ['cache'] ?? false);
		}
	}

	public static function getDbOffset(&$config, $offset = null) {
		return $offset ? ($config ['database'] [$offset] ?? ($config ['database'] ?? [ ])) : ($config ['database'] ['default'] ?? $config ['database']);
	}

	/**
	 * Returns true if the connection to the database is established
	 *
	 * @return boolean
	 */
	public static function isConnected($offset = 'default') {
		$db = self::$db [$offset] ?? false;
		return $db && ($db instanceof Database) && $db->isConnected ();
	}

	/**
	 * Sets the transformer operation
	 *
	 * @param string $op
	 */
	public static function setTransformerOp($op) {
		self::$transformerOp = $op;
	}

	/**
	 * Closes the active pdo connection to the database
	 */
	public static function closeDb($offset = 'default') {
		$db = self::$db [$offset] ?? false;
		if ($db !== false) {
			$db->close ();
		}
	}

	/**
	 * Defines the database connection to use for $model class
	 *
	 * @param string $model a model class
	 * @param string $database a database connection defined in config.php
	 */
	public static function setModelDatabase($model, $database = 'default') {
		self::$modelsDatabase [$model] = $database;
	}

	/**
	 * Defines the database connections to use for models classes
	 *
	 * @param array $modelsDatabase
	 */
	public static function setModelsDatabases($modelsDatabase) {
		self::$modelsDatabase = $modelsDatabase;
	}

	/**
	 * Returns the database instance defined at $offset key in config
	 *
	 * @param string $offset
	 * @return \Ubiquity\db\Database
	 */
	public static function getDatabase($offset = 'default') {
		if (! isset ( self::$db [$offset] )) {
			self::startDatabase ( Startup::$config, $offset );
		}
		SqlUtils::$quote = self::$db [$offset]->quote??'';
		return self::$db [$offset]??null;
	}

	public static function getDatabases() {
		$config = Startup::getConfig ();
		if (isset ( $config ['database'] )) {
			if (isset ( $config ['database'] ['dbName'] )) {
				return [ 'default' ];
			} else {
				return \array_keys ( $config ['database'] );
			}
		}
		return [ ];
	}

	public static function updateDatabaseParams(array &$config, array $parameters, $offset = 'default') {
		if ($offset === 'default') {
			if (isset ( $config ['database'] [$offset] )) {
				foreach ( $parameters as $k => $param ) {
					$config ['database'] [$offset] [$k] = $param;
				}
			} else {
				foreach ( $parameters as $k => $param ) {
					$config ['database'] [$k] = $param;
				}
			}
		} else {
			if (isset ( $config ['database'] [$offset] )) {
				foreach ( $parameters as $k => $param ) {
					$config ['database'] [$offset] [$k] = $param;
				}
			}
		}
	}

	public static function start() {
		self::$modelsDatabase = CacheManager::getModelsDatabases ();
	}

	public static function getDbCacheInstance($model) {
		$db = static::$db [self::$modelsDatabase [$model] ?? 'default'];
		return $db->getCacheInstance ();
	}

	public static function warmupCache($className, $condition = '', $included = false, $parameters = [ ]) {
		$objects = self::getAll ( $className, $condition, $included, $parameters );
		foreach ( $objects as $o ) {
			self::$cache->store ( $className, OrmUtils::getKeyValues ( $o ), $o );
		}
		self::$cache->optimize ();
		$offset = self::$modelsDatabase [$className] ?? 'default';
		$db = self::$db [$offset];
		$db->close ();
		unset ( self::$db [$offset] );
	}

	public static function setCache(AbstractDAOCache $cache) {
		self::$cache = $cache;
	}

	/**
	 *
	 * @return \Ubiquity\cache\dao\AbstractDAOCache
	 */
	public static function getCache() {
		return static::$cache;
	}

	/**
	 * @return array
	 */
	public static function getModelsDatabase(): array {
		return self::$modelsDatabase;
	}
}

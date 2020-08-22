<?php

namespace Ubiquity\orm\traits;

use Ubiquity\cache\CacheManager;
use Ubiquity\cache\dao\AbstractDAOCache;
use Ubiquity\controllers\Startup;
use Ubiquity\db\AbstractDatabase;
use Ubiquity\db\Database;
use Ubiquity\db\SqlUtils;
use Ubiquity\exceptions\DAOException;
use Ubiquity\log\Logger;

/**
 * Ubiquity\orm\traits$DAOCommon
 * This class is part of Ubiquity
 *
 * @author jc
 * @version 1.0.0
 *
 */
trait DAOCommonTrait {

	/**
	 *
	 * @var AbstractDatabase
	 */
	public static $db;
	public static $useTransformers = false;
	public static $transformerOp = 'transform';
	protected static $modelsDatabase = [ ];

	/**
	 *
	 * @var AbstractDAOCache
	 */
	protected static $cache;

	/**
	 * Returns the database instance defined at $offset key in config
	 *
	 * @param ?array $config
	 * @param string $offset
	 * @return \Ubiquity\db\Database
	 */
	public static function getSqlOrNosqlDatabase($config = null, $offset = 'default') {
		if (! isset ( self::$db [$offset] )) {
			$config ??= Startup::$config;
			$db = $offset ? ($config ['database'] [$offset] ?? ($config ['database'] ?? [ ])) : ($config ['database'] ['default'] ?? $config ['database']);
			$wrapper = $db ['wrapper'];
			$databaseClass = $wrapper::$databaseClass;
			$dbInstance = self::$db [$offset] = new $databaseClass ( $db ['wrapper'] ?? \Ubiquity\db\providers\pdo\PDOWrapper::class, $db ['type'], $db ['dbName'], $db ['serverName'] ?? '127.0.0.1', $db ['port'] ?? 3306, $db ['user'] ?? 'root', $db ['password'] ?? '', $db ['options'] ?? [ ], $db ['cache'] ?? false, self::$pool );
			try {
				$dbInstance->connect ();
			} catch ( \Exception $e ) {
				Logger::error ( "DAO", $e->getMessage () );
				throw new DAOException ( $e->getMessage (), $e->getCode (), $e->getPrevious () );
			}
		}
		if (self::$db [$offset] instanceof Database) {
			SqlUtils::$quote = self::$db [$offset]->quote;
		}
		return self::$db [$offset];
	}

	public static function getDAOClass($config = null, $offset = 'default') {
		$config ??= Startup::$config;
		$db = self::getDbOffset ( $config, $offset );
		$wrapper = $db ['wrapper'];
		$databaseClass = $wrapper::$databaseClass;
		if ($databaseClass === '\\Ubiquity\\db\\Database') {
			return '\\Ubiquity\\orm\\DAO';
		}
		return '\\Ubiquity\\orm\\DAONosql';
	}

	public static function getDAOClassFromModel($model) {
		return self::getDAOClass ( null, self::$modelsDatabase [$model] ?? 'default');
	}

	public static function getDb($model) {
		return self::getDatabase ( self::$modelsDatabase [$model] ?? 'default');
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
		return $db && ($db instanceof AbstractDatabase) && $db->isConnected ();
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
	 * Closes the active connection to the database
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

	protected static function applyTransformers($transformers, &$row, $memberNames) {
		foreach ( $transformers as $member => $transformer ) {
			$field = \array_search ( $member, $memberNames );
			$transform = self::$transformerOp;
			$row [$field] = $transformer::{$transform} ( $row [$field] );
		}
	}

	public static function start() {
		self::$modelsDatabase = CacheManager::getModelsDatabases ();
	}

	public static function getDbCacheInstance($model) {
		$db = static::$db [self::$modelsDatabase [$model] ?? 'default'];
		return $db->getCacheInstance ();
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
}


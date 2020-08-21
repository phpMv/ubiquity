<?php

namespace Ubiquity\orm\traits;

use Ubiquity\cache\CacheManager;
use Ubiquity\cache\dao\AbstractDAOCache;
use Ubiquity\controllers\Startup;
use Ubiquity\db\AbstractDatabase;

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


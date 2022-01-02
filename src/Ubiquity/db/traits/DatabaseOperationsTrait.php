<?php

namespace Ubiquity\db\traits;

use Ubiquity\cache\database\DbCache;
use Ubiquity\db\SqlUtils;
use Ubiquity\exceptions\CacheException;
use Ubiquity\log\Logger;

/**
 * Ubiquity\db\traits$DatabaseOperationsTrait
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.5
 * @property mixed $cache
 * @property array $options
 * @property \Ubiquity\db\providers\AbstractDbWrapper $wrapperObject
 */
trait DatabaseOperationsTrait {

	abstract public function getDSN();

	public function getDbObject() {
		return $this->wrapperObject->getDbInstance ();
	}

	public function _connect() {
		$this->wrapperObject->connect ( $this->dbType, $this->dbName, $this->serverName, $this->port, $this->user, $this->password, $this->options );
	}

	/**
	 * Executes an SQL statement, returning a result set as a statement object
	 *
	 * @param string $sql
	 * @return object|boolean
	 */
	public function query($sql) {
		return $this->wrapperObject->query ( $sql );
	}

	/**
	 *
	 * @param string $tableName
	 * @param string $condition
	 * @param array|string $fields
	 * @param array $parameters
	 * @param boolean|null $useCache
	 * @return array
	 */
	public function prepareAndExecute($tableName, $condition, $fields, $parameters = null, $useCache = false, $one = false) {
		$result = false;
		if ($cache = ($useCache!==false && (DbCache::$active || $useCache))) {
			$result = $this->getCacheValue ( $tableName, $condition, $parameters, $cKey );
		}
		if ($result === false) {
			$quote = SqlUtils::$quote;
			$sql="SELECT {$fields} FROM {$quote}{$tableName}{$quote} {$condition}";
			Logger::info ( 'Database', $sql, 'prepareAndExecute', $parameters );
			$result = $this->wrapperObject->_optPrepareAndExecute ( $sql, $parameters, $one );
			if ($cache) {
				$this->cache->store ( $tableName, $cKey, $result );
			}
		}
		return $result;
	}

	private function getCacheValue($tableName, $condition, $parameters, &$cKey) {
		$cKey = $condition;
		if (\is_array ( $parameters )) {
			$cKey .= \implode ( ',', $parameters );
		}
		try {
			$result = $this->cache->fetch ( $tableName, $cKey );
			Logger::info ( 'Cache', "fetching cache for table {$tableName} with condition : {$condition}", 'Database::prepareAndExecute', $parameters );
		} catch ( \Exception $e ) {
			throw new CacheException ( "Cache is not created in Database constructor" );
		}
		return $result;
	}

	public function _optExecuteAndFetch($statement, $tableName, $condition, $parameters = null, $useCache = false, $one = false) {
		$result = false;
		if ($cache = ($useCache!==false && (DbCache::$active || $useCache))) {
			$result = $this->getCacheValue ( $tableName, $condition, $parameters, $cKey );
		}
		if ($result === false) {
			$result = $this->wrapperObject->_optExecuteAndFetch ( $statement, $parameters, $one );
			if ($cache) {
				$this->cache->store ( $tableName, $cKey, $result );
			}
		}
		return $result;
	}

	public function _optExecuteAndFetchNoCache($statement, $parameters = null, $one = false) {
		return $this->wrapperObject->_optExecuteAndFetch ( $statement, $parameters, $one );
	}

	public function getDaoPreparedStatement($tableName, $condition, $fields) {
		$quote = SqlUtils::$quote;
		return $this->wrapperObject->prepareStatement ( "SELECT {$fields} FROM {$quote}{$tableName}{$quote} {$condition}" );
	}

	public function prepareAndExecuteNoCache($tableName, $condition, $fields, $parameters = null) {
		$quote = SqlUtils::$quote;
		return $this->wrapperObject->_optPrepareAndExecute ( "SELECT {$fields} FROM {$quote}{$tableName}{$quote} {$condition}", $parameters );
	}

	public function storeCache() {
		$this->cache->storeDeferred ();
	}

	public function prepareAndFetchAll($sql, $parameters = null, $mode = null) {
		return $this->wrapperObject->fetchAll ( $this->wrapperObject->_getStatement ( $sql ), $parameters, $mode );
	}

	public function prepareAndFetchOne($sql, $parameters = null, $mode = null) {
		return $this->wrapperObject->fetchOne ( $this->wrapperObject->_getStatement ( $sql ), $parameters, $mode );
	}

	public function prepareAndFetchAllColumn($sql, $parameters = null, $column = null) {
		return $this->wrapperObject->fetchAllColumn ( $this->wrapperObject->_getStatement ( $sql ), $parameters, $column );
	}

	public function prepareAndFetchColumn($sql, $parameters = null, $columnNumber = 0) {
		$statement = $this->wrapperObject->_getStatement ( $sql );
		if ($statement->execute ( $parameters )) {
			Logger::info ( 'Database', $sql, 'prepareAndFetchColumn', $parameters );
			return $statement->fetchColumn ( $columnNumber );
		}
		return false;
	}

	/**
	 *
	 * @param string $sql
	 * @return object statement
	 */
	private function getStatement($sql) {
		return $this->wrapperObject->_getStatement ( $sql );
	}

	/**
	 *
	 * @param string $sql
	 * @return object statement
	 */
	public function getUpdateStatement($sql) {
		return $this->wrapperObject->_getStatement ( $sql );
	}

	/**
	 * Prepares a statement and execute a query for update (INSERT, UPDATE, DELETE...)
	 *
	 * @param string $sql
	 * @param array|null $parameters
	 * @return boolean
	 */
	public function prepareAndExecuteUpdate($sql, $parameters = null) {
		return $this->getUpdateStatement ( $sql )->execute ( $parameters );
	}

	/**
	 * Execute an SQL statement and return the number of affected rows (INSERT, UPDATE or DELETE)
	 *
	 * @param string $sql
	 * @return int the number of rows that were modified or deleted by the SQL statement you issued
	 */
	public function execute($sql) {
		return $this->wrapperObject->execute ( $sql );
	}

	/**
	 * Prepares a statement for execution and returns a statement object
	 *
	 * @param string $sql
	 * @return object|boolean
	 */
	public function prepareStatement($sql) {
		return $this->wrapperObject->prepareStatement ( $sql );
	}

	/**
	 * Prepares and returns a statement for execution and gives it a name.
	 *
	 * @param string $name
	 * @param string $sql
	 * @return mixed
	 */
	public function prepareNamedStatement(string $name, string $sql) {
		return $this->wrapperObject->prepareNamedStatement ( $name, $sql );
	}

	/**
	 * Returns the statement corresponding to the name.
	 *
	 * @param string $name
	 * @param ?string $sql
	 * @return mixed
	 */
	public function getNamedStatement(string $name, ?string $sql = null) {
		return $this->wrapperObject->getNamedStatement ( $name, $sql );
	}

	/**
	 * Sets $value to $parameter
	 *
	 * @param mixed $statement
	 * @param string $parameter
	 * @param mixed $value
	 * @return boolean
	 */
	public function bindValueFromStatement($statement, $parameter, $value) {
		return $this->wrapperObject->bindValueFromStatement ( $statement, $parameter, $value );
	}

	/**
	 * Returns the last insert id
	 *
	 * @return string
	 */
	public function lastInserId($name = null) {
		return $this->wrapperObject->lastInsertId ( $name );
	}

	/**
	 * Returns the number of records in $tableName matching with the condition passed as a parameter
	 *
	 * @param string $tableName
	 * @param string $condition Part following the WHERE of an SQL statement
	 */
	public function count($tableName, $condition = '') {
		if ($condition != ''){
			$condition = ' WHERE ' . $condition;
		}
		return $this->wrapperObject->queryColumn ( 'SELECT COUNT(*) FROM ' . $tableName . $condition );
	}

	public function queryColumn($query, $columnNumber = null) {
		return $this->wrapperObject->queryColumn ( $query, $columnNumber );
	}

	public function fetchAll($query, $mode = null) {
		return $this->wrapperObject->queryAll ( $query, $mode );
	}

	public function isConnected() {
		return ($this->wrapperObject !== null && $this->ping ());
	}

	public function ping() {
		return ($this->wrapperObject && $this->wrapperObject->ping ());
	}
}

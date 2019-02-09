<?php

namespace Ubiquity\db\traits;

use Ubiquity\log\Logger;
use Ubiquity\cache\database\DbCache;
use Ubiquity\exceptions\CacheException;
use Ubiquity\db\SqlUtils;

/**
 * Ubiquity\db\traits$DatabaseOperationsTrait
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 * @property mixed $cache
 * @property array $options
 */
trait DatabaseOperationsTrait {

	/**
	 *
	 * @var \PDO
	 */
	protected $pdoObject;
	private $statements = [ ];

	abstract public function getDSN();

	public function getPdoObject() {
		return $this->pdoObject;
	}

	public function _connect() {
		$this->options [\PDO::ATTR_ERRMODE] = \PDO::ERRMODE_EXCEPTION;
		$this->pdoObject = new \PDO ( $this->getDSN (), $this->user, $this->password, $this->options );
	}

	/**
	 * Executes an SQL statement, returning a result set as a PDOStatement object
	 *
	 * @param string $sql
	 * @return \PDOStatement|boolean
	 */
	public function query($sql) {
		return $this->pdoObject->query ( $sql );
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
	public function prepareAndExecute($tableName, $condition, $fields, $parameters = null, $useCache = NULL) {
		$cache = ((DbCache::$active && $useCache !== false) || (! DbCache::$active && $useCache === true));
		$result = false;
		if ($cache) {
			$cKey = $condition;
			if (is_array ( $parameters )) {
				$cKey .= implode ( ",", $parameters );
			}
			try {
				$result = $this->cache->fetch ( $tableName, $cKey );
				Logger::info ( "Cache", "fetching cache for table {$tableName} with condition : {$condition}", "Database::prepareAndExecute", $parameters );
			} catch ( \Exception $e ) {
				throw new CacheException ( "Cache is not created in Database constructor" );
			}
		}
		if ($result === false) {
			if ($fields = SqlUtils::getFieldList ( $fields, $tableName )) {
				$result = $this->prepareAndFetchAll ( "SELECT {$fields} FROM `" . $tableName . "`" . $condition, $parameters );
				if ($cache) {
					$this->cache->store ( $tableName, $cKey, $result );
				}
			}
		}
		return $result;
	}

	public function prepareAndFetchAll($sql, $parameters = null) {
		$result = false;
		$statement = $this->getStatement ( $sql );
		if ($statement->execute ( $parameters )) {
			Logger::info ( "Database", $sql, "prepareAndFetchAll", $parameters );
			$result = $statement->fetchAll ();
		}
		$statement->closeCursor ();
		return $result;
	}

	public function prepareAndFetchAllColumn($sql, $parameters = null, $column = null) {
		$result = false;
		$statement = $this->getStatement ( $sql );
		if ($statement->execute ( $parameters )) {
			Logger::info ( "Database", $sql, "prepareAndFetchAllColumn", $parameters );
			$result = $statement->fetchAll ( \PDO::FETCH_COLUMN, $column );
		}
		$statement->closeCursor ();
		return $result;
	}

	public function prepareAndFetchColumn($sql, $parameters = null, $columnNumber = null) {
		$statement = $this->getStatement ( $sql );
		if ($statement->execute ( $parameters )) {
			Logger::info ( "Database", $sql, "prepareAndFetchColumn", $parameters );
			return $statement->fetchColumn ( $columnNumber );
		}
		return false;
	}

	/**
	 *
	 * @param string $sql
	 * @return \PDOStatement
	 */
	private function getStatement($sql) {
		if (! isset ( $this->statements [$sql] )) {
			$this->statements [$sql] = $this->pdoObject->prepare ( $sql );
			$this->statements [$sql]->setFetchMode ( \PDO::FETCH_ASSOC );
		}
		return $this->statements [$sql];
	}

	/**
	 * Execute an SQL statement and return the number of affected rows (INSERT, UPDATE or DELETE)
	 *
	 * @param string $sql
	 * @return int the number of rows that were modified or deleted by the SQL statement you issued
	 */
	public function execute($sql) {
		return $this->pdoObject->exec ( $sql );
	}

	/**
	 * Prepares a statement for execution and returns a statement object
	 *
	 * @param String $sql
	 * @return \PDOStatement|boolean
	 */
	public function prepareStatement($sql) {
		return $this->pdoObject->prepare ( $sql );
	}

	/**
	 * Sets $value to $parameter
	 *
	 * @param \PDOStatement $statement
	 * @param String $parameter
	 * @param mixed $value
	 * @return boolean
	 */
	public function bindValueFromStatement(\PDOStatement $statement, $parameter, $value) {
		return $statement->bindValue ( ":" . $parameter, $value );
	}

	/**
	 * Returns the last insert id
	 *
	 * @return string
	 */
	public function lastInserId() {
		return $this->pdoObject->lastInsertId ();
	}

	public function getTablesName() {
		$sql = 'SHOW TABLES';
		$query = $this->pdoObject->query ( $sql );
		return $query->fetchAll ( \PDO::FETCH_COLUMN );
	}

	/**
	 * Returns the number of records in $tableName that respects the condition passed as a parameter
	 *
	 * @param string $tableName
	 * @param string $condition
	 *        	Partie suivant le WHERE d'une instruction SQL
	 */
	public function count($tableName, $condition = '') {
		if ($condition != '')
			$condition = " WHERE " . $condition;
		return $this->query ( "SELECT COUNT(*) FROM " . $tableName . $condition )->fetchColumn ();
	}

	public function queryColumn($query, $columnNumber = null) {
		return $this->query ( $query )->fetchColumn ( $columnNumber );
	}

	public function fetchAll($query) {
		return $this->query ( $query )->fetchAll ();
	}

	public function isConnected() {
		return ($this->pdoObject !== null && $this->pdoObject instanceof \PDO && $this->ping ());
	}

	public function ping() {
		return ($this->pdoObject && 1 === intval ( $this->pdoObject->query ( 'SELECT 1' )->fetchColumn ( 0 ) ));
	}
}


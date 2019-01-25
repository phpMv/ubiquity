<?php

namespace Ubiquity\db;

use Ubiquity\cache\database\DbCache;
use Ubiquity\exceptions\CacheException;
use Ubiquity\log\Logger;

/**
 * PDO database class
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.3
 */
class Database {
	private $dbType;
	private $serverName;
	private $port;
	private $dbName;
	private $user;
	private $password;
	private $pdoObject;
	private $statements = [ ];
	private $cache;
	private $options;

	/**
	 * Constructor
	 *
	 * @param string $dbName
	 * @param string $serverName
	 * @param string $port
	 * @param string $user
	 * @param string $password
	 * @param array $options
	 * @param boolean|string $cache
	 */
	public function __construct($dbType, $dbName, $serverName = "127.0.0.1", $port = "3306", $user = "root", $password = "", $options = [], $cache = false) {
		$this->dbType = $dbType;
		$this->dbName = $dbName;
		$this->serverName = $serverName;
		$this->port = $port;
		$this->user = $user;
		$this->password = $password;
		if (isset ( $options ["quote"] ))
			SqlUtils::$quote = $options ["quote"];
		$this->options = $options;
		if ($cache !== false) {
			if (\is_callable ( $cache )) {
				$this->cache = $cache ();
			} else {
				if (\class_exists ( $cache )) {
					$this->cache = new $cache ();
				} else {
					throw new CacheException ( $cache . " is not a valid value for database cache" );
				}
			}
		}
	}

	/**
	 * Creates the PDO instance and realize a safe connection
	 *
	 * @return boolean true if connection is established
	 */
	public function connect() {
		try {
			$this->_connect ();
			return true;
		} catch ( \PDOException $e ) {
			echo $e->getMessage ();
			return false;
		}
	}

	public function _connect() {
		$this->options [\PDO::ATTR_ERRMODE] = \PDO::ERRMODE_EXCEPTION;
		$this->pdoObject = new \PDO ( $this->getDSN (), $this->user, $this->password, $this->options );
	}

	public function getDSN() {
		return $this->dbType . ':dbname=' . $this->dbName . ';host=' . $this->serverName . ';charset=UTF8;port=' . $this->port;
	}

	/**
	 * Executes an SQL statement, returning a result set as a PDOStatement object
	 *
	 * @param string $sql
	 * @return \PDOStatement
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
	 *
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function getServerName() {
		return $this->serverName;
	}

	public function setServerName($serverName) {
		$this->serverName = $serverName;
	}

	/**
	 * Prepares a statement for execution and returns a statement object
	 *
	 * @param String $sql
	 * @return \PDOStatement
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
	 * @return integer
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

	public function setDbType($dbType) {
		$this->dbType = $dbType;
		return $this;
	}

	public function ping() {
		return ($this->pdoObject && 1 === intval ( $this->pdoObject->query ( 'SELECT 1' )->fetchColumn ( 0 ) ));
	}

	/**
	 *
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function getPort() {
		return $this->port;
	}

	/**
	 *
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function getDbName() {
		return $this->dbName;
	}

	/**
	 *
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function getUser() {
		return $this->user;
	}

	public function getPdoObject() {
		return $this->pdoObject;
	}

	public static function getAvailableDrivers() {
		return \PDO::getAvailableDrivers ();
	}

	/**
	 *
	 * @return mixed
	 * @codeCoverageIgnore
	 */
	public function getDbType() {
		return $this->dbType;
	}

	/**
	 *
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function getPassword() {
		return $this->password;
	}

	/**
	 *
	 * @return array
	 * @codeCoverageIgnore
	 */
	public function getOptions() {
		return $this->options;
	}

	/**
	 *
	 * @param string $port
	 */
	public function setPort($port) {
		$this->port = $port;
	}

	/**
	 *
	 * @param string $dbName
	 */
	public function setDbName($dbName) {
		$this->dbName = $dbName;
	}

	/**
	 *
	 * @param string $user
	 */
	public function setUser($user) {
		$this->user = $user;
	}

	/**
	 *
	 * @param string $password
	 */
	public function setPassword($password) {
		$this->password = $password;
	}

	/**
	 *
	 * @param array $options
	 */
	public function setOptions($options) {
		$this->options = $options;
	}
}

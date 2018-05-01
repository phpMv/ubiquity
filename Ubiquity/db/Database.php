<?php

namespace Ubiquity\db;

use Ubiquity\cache\database\DbCache;
use Ubiquity\exceptions\CacheException;

/**
 * PDO Database class
 *
 * @author jc
 * @version 1.0.0.3
 * @package db
 *
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
	public function __construct($dbType, $dbName, $serverName = "localhost", $port = "3306", $user = "root", $password = "", $options = [], $cache = false) {
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
	 * @return boolean true if connection is established
	 */
	public function connect() {
		try{
			$this->_connect();
			return true;
		}catch (\PDOException $e){
			return false;
		}
	}
	
	public function _connect(){
		$this->options[\PDO::ATTR_ERRMODE]=\PDO::ERRMODE_EXCEPTION;
		$this->pdoObject = new \PDO ( $this->getDSN(), $this->user, $this->password, $this->options );
	}
	
	public function getDSN(){
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
	 * @param boolean|null $useCache
	 * @return array
	 */
	public function prepareAndExecute($tableName, $condition, $fields, $useCache = NULL) {
		$cache = (DbCache::$active && $useCache !== false) || (! DbCache::$active && $useCache === true);
		$result = false;
		if ($cache) {
			$result = $this->cache->fetch ( $tableName, $condition );
		}
		if ($result === false) {
			$fields = SqlUtils::getFieldList ( $fields, $tableName );
			$statement = $this->getStatement ( "SELECT {$fields} FROM " . $tableName . $condition );
			$statement->execute ();
			$result = $statement->fetchAll ();
			$statement->closeCursor ();
			if ($cache) {
				$this->cache->store ( $tableName, $condition, $result );
			}
		}
		return $result;
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
	
	public function queryColumn($query){
		return $this->query ( $query )->fetchColumn ();
	}
	
	public function isConnected() {
		return ($this->pdoObject !== null && $this->pdoObject instanceof \PDO && $this->ping());
	}

	public function setDbType($dbType) {
		$this->dbType = $dbType;
		return $this;
	}
	
	public function ping() {
		return (1 === intval($this->pdoObject->query('SELECT 1')->fetchColumn(0)));
	}

	public function getPort() {
		return $this->port;
	}

	public function getDbName() {
		return $this->dbName;
	}

	public function getUser() {
		return $this->user;
	}

	public function getPdoObject() {
		return $this->pdoObject;
	}
	
	public static function getAvailableDrivers(){
		return \PDO::getAvailableDrivers();
	}
}

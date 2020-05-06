<?php

namespace Ubiquity\db\providers\pdo;

use Ubiquity\db\providers\AbstractDbWrapper;
use Ubiquity\exceptions\DBException;

/**
 * Ubiquity\db\providers$PDOWrapper
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.3
 * @property \PDO $dbInstance
 *
 */
class PDOWrapper extends AbstractDbWrapper {
	protected static $savepointsDrivers = [ 'pgsql' => true,'mysql' => true,'sqlite' => true ];
	private static $quotes = [ 'mysql' => '`','sqlite' => '"','pgsql' => '' ];
	protected $driversMetasClasses = [ 'mysql' => '\\Ubiquity\\db\\providers\\pdo\\drivers\\MysqlDriverMetas','pgsql' => '\\Ubiquity\\db\\providers\\pdo\\drivers\\PgsqlDriverMetas','sqlite' => '\\Ubiquity\\db\\providers\\pdo\\drivers\\SqliteDriverMetas' ];
	protected $transactionLevel = 0;
	protected $dbType;
	protected $driverMetaDatas;

	/**
	 *
	 * @throws DBException
	 * @return \Ubiquity\db\providers\pdo\drivers\AbstractDriverMetaDatas
	 */
	protected function getDriverMetaDatas() {
		if (! isset ( $this->driverMetaDatas )) {
			if (isset ( $this->driversMetasClasses [$this->dbType] )) {
				$metaClass = $this->driversMetasClasses [$this->dbType];
				$this->driverMetaDatas = new $metaClass ( $this->dbInstance );
			} else {
				throw new DBException ( "{$this->dbType} driver is not yet implemented!" );
			}
		}
		return $this->driverMetaDatas;
	}

	public function __construct($dbType = 'mysql') {
		$this->quote = self::$quotes [$dbType] ?? '';
		$this->dbType = $dbType;
	}

	public function fetchAllColumn($statement, array $values = null, string $column = null) {
		$result = false;
		if ($statement->execute ( $values )) {
			$result = $statement->fetchAll ( \PDO::FETCH_COLUMN, $column );
		}
		$statement->closeCursor ();
		return $result;
	}

	public function lastInsertId($name = null) {
		return $this->dbInstance->lastInsertId ( $name );
	}

	public function fetchAll($statement, array $values = null, $mode = null) {
		$result = false;
		if ($statement->execute ( $values )) {
			$result = $statement->fetchAll ( $mode );
		}
		$statement->closeCursor ();
		return $result;
	}

	public function fetchOne($statement, array $values = null, $mode = null) {
		$result = false;
		if ($statement->execute ( $values )) {
			$result = $statement->fetch ( $mode );
		}
		$statement->closeCursor ();
		return $result;
	}

	public static function getAvailableDrivers() {
		return \PDO::getAvailableDrivers ();
	}

	public function prepareStatement(string $sql) {
		return $this->dbInstance->prepare ( $sql );
	}

	public function fetchColumn($statement, array $values = null, int $columnNumber = null) {
		if ($statement->execute ( $values )) {
			return $statement->fetchColumn ( $columnNumber );
		}
		return false;
	}

	public function getStatement($sql) {
		$st = $this->dbInstance->prepare ( $sql );
		$st->setFetchMode ( \PDO::FETCH_ASSOC );
		return $st;
	}

	public function execute($sql) {
		return $this->dbInstance->exec ( $sql );
	}

	public function connect(string $dbType, $dbName, $serverName, string $port, string $user, string $password, array $options) {
		$options [\PDO::ATTR_ERRMODE] = \PDO::ERRMODE_EXCEPTION;
		$this->dbInstance = new \PDO ( $this->getDSN ( $serverName, $port, $dbName, $dbType ), $user, $password, $options );
	}

	public function getDSN(string $serverName, string $port, string $dbName, string $dbType = 'mysql') {
		$charsetString = [ 'mysql' => 'charset=UTF8','pgsql' => 'options=\'--client_encoding=UTF8\'','sqlite' => '' ] [$dbType] ?? 'charset=UTF8';
		if ($dbType === 'sqlite') {
			return "sqlite:{$dbName}";
		}
		return $dbType . ":dbname={$dbName};host={$serverName};{$charsetString};port=" . $port;
	}

	public function bindValueFromStatement($statement, $parameter, $value) {
		return $statement->bindValue ( ":" . $parameter, $value );
	}

	public function query(string $sql) {
		return $this->dbInstance->query ( $sql );
	}

	public function queryAll(string $sql, int $fetchStyle = null) {
		return $this->dbInstance->query ( $sql )->fetchAll ( $fetchStyle );
	}

	public function queryColumn(string $sql, int $columnNumber = null) {
		return $this->dbInstance->query ( $sql )->fetchColumn ( $columnNumber );
	}

	public function executeStatement($statement, array $values = null) {
		return $statement->execute ( $values );
	}

	public function getTablesName() {
		return $this->getDriverMetaDatas ()->getTablesName ();
	}

	public function statementRowCount($statement) {
		return $statement->rowCount ();
	}

	public function inTransaction() {
		return $this->dbInstance->inTransaction ();
	}

	public function commit() {
		return $this->dbInstance->commit ();
	}

	public function rollBack() {
		return $this->dbInstance->rollBack ();
	}

	public function beginTransaction() {
		return $this->dbInstance->beginTransaction ();
	}

	public function savePoint($level) {
		$this->dbInstance->exec ( 'SAVEPOINT LEVEL' . $level );
	}

	public function releasePoint($level) {
		$this->dbInstance->exec ( 'RELEASE SAVEPOINT LEVEL' . $level );
	}

	public function rollbackPoint($level) {
		$this->dbInstance->exec ( 'ROLLBACK TO SAVEPOINT LEVEL' . $level );
	}

	public function nestable() {
		return isset ( self::$savepointsDrivers [$this->dbType] );
	}

	public function ping() {
		return ($this->dbInstance != null) && (1 === \intval ( $this->queryColumn ( 'SELECT 1', 0 ) ));
	}

	public function getPrimaryKeys($tableName) {
		return $this->getDriverMetaDatas ()->getPrimaryKeys ( $tableName );
	}

	public function getForeignKeys($tableName, $pkName, $dbName = null) {
		return $this->getDriverMetaDatas ()->getForeignKeys ( $tableName, $pkName, $dbName );
	}

	public function getFieldsInfos($tableName) {
		return $this->getDriverMetaDatas ()->getFieldsInfos ( $tableName );
	}

	public function _optPrepareAndExecute($sql, array $values = null) {
		$statement = $this->_getStatement ( $sql );
		$result = false;
		if ($statement->execute ( $values )) {
			$result = $statement->fetchAll ( \PDO::FETCH_ASSOC );
		}
		$statement->closeCursor ();
		return $result;
	}

	public function quoteValue($value, $type = 2) {
		return $this->dbInstance->quote ( $value, $type );
	}

	public function getRowNum(string $tableName, string $pkName, string $condition): int {
		return $this->getDriverMetaDatas ()->getRowNum ( $tableName, $pkName, $condition );
	}

	public function groupConcat(string $fields, string $separator): string {
		return $this->getDriverMetaDatas ()->groupConcat ( $fields, $separator );
	}

	public function toStringOperator() {
		return $this->getDriverMetaDatas ()->toStringOperator ();
	}
}

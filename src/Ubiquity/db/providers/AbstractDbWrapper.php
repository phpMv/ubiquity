<?php

namespace Ubiquity\db\providers;

use Ubiquity\exceptions\DBException;

/**
 * Ubiquity\db\providers$AbstractDbWrapper
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.6
 *
 */
abstract class AbstractDbWrapper {
	protected $dbInstance;
	protected $statements;
	protected $operations=[
			DbOperations::CREATE_DATABASE=>'CREATE DATABASE {name}',
			DbOperations::CREATE_TABLE=>'CREATE TABLE {name} ({fields}) {attributes}',
			DbOperations::SELECT_DB=>'USE {name}',
			DbOperations::FIELD=>'{name} {type} {extra}',
			DbOperations::ALTER_TABLE=>'ALTER TABLE {tableName} {alter}',
			DbOperations::FOREIGN_KEY=>'ALTER TABLE {tableName} ADD CONSTRAINT {fkName} FOREIGN KEY ({fkFieldName}) REFERENCES {referencesTableName} ({referencesFieldName}) ON DELETE {onDelete} ON UPDATE {onUpdate}',
			DbOperations::ALTER_TABLE_KEY=>'ALTER TABLE {tableName} ADD {type} KEY ({pkFields})',
			DbOperations::AUTO_INC=>'ALTER TABLE {tableName} MODIFY {fieldName} AUTO_INCREMENT, AUTO_INCREMENT={value}',
			DbOperations::MODIFY_FIELD=>'ALTER TABLE {tableName} MODIFY {fieldName} {attributes}',
			DbOperations::ADD_FIELD=>'ALTER TABLE {tableName} ADD {fieldName} {attributes}'
	];

	const PHP_TYPES = [ 'string' => true,'bool' => true,'float' => true,'int' => true ];
	
	public $quote;

	abstract public function query(string $sql);

	abstract public function queryAll(string $sql, int $fetchStyle = null);

	abstract public function queryColumn(string $sql, int $columnNumber = null);

	abstract public static function getAvailableDrivers();

	public function _getStatement(string $sql) {
		return $this->statements [\md5 ( $sql )] ??= $this->getStatement ( $sql );
	}

	public function prepareNamedStatement(string $name, string $sql) {
		return $this->statements [$name] = $this->getStatement ( $sql );
	}

	public function getNamedStatement(string $name, ?string $sql = null) {
		return $this->statements [$name] ??= $this->getStatement ( $sql );
	}

	abstract public function getStatement(string $sql);

	abstract public function connect(string $dbType, $dbName, $serverName, string $port, string $user, string $password, array $options);

	abstract public function getDSN(string $serverName, string $port, string $dbName, string $dbType = 'mysql');

	abstract public function execute(string $sql);

	abstract public function prepareStatement(string $sql);

	abstract public function executeStatement($statement, array $values = null);

	abstract public function statementRowCount($statement);

	abstract public function lastInsertId($name = null);

	/**
	 * Used by DAO
	 *
	 * @param mixed $statement
	 * @param string $parameter
	 * @param mixed $value
	 */
	abstract public function bindValueFromStatement($statement, $parameter, $value);

	abstract public function fetchColumn($statement, array $values = null, int $columnNumber = null);

	abstract public function fetchAll($statement, array $values = null, $mode = null);

	abstract public function fetchOne($statement, array $values = null, $mode = null);

	abstract public function fetchAllColumn($statement, array $values = null, string $column = null);

	abstract public function getTablesName();

	abstract public function beginTransaction();

	abstract public function commit();

	abstract public function inTransaction();

	abstract public function rollBack();

	abstract public function nestable();

	abstract public function savePoint($level);

	abstract public function releasePoint($level);

	abstract public function rollbackPoint($level);

	abstract public function ping();

	abstract public function getPrimaryKeys($tableName);

	abstract public function getFieldsInfos($tableName);

	abstract public function getForeignKeys($tableName, $pkName, $dbName = null);

	abstract public function _optPrepareAndExecute($sql, array $values = null, $one = false);

	public function _optExecuteAndFetch($statement, array $values = null, $one = false) {
	}

	abstract public function getRowNum(string $tableName, string $pkName, string $condition): int;

	abstract public function groupConcat(string $fields, string $separator): string;

	public function toStringOperator() {
		return '';
	}

	public function close() {
		$this->statements = [ ];
		$this->dbInstance = null;
	}

	/**
	 *
	 * @return object
	 */
	public function getDbInstance() {
		return $this->dbInstance;
	}

	/**
	 *
	 * @param object $dbInstance
	 */
	public function setDbInstance($dbInstance) {
		$this->dbInstance = $dbInstance;
	}

	public function quoteValue($value, $type = 2) {
		return "'" . \addslashes ( $value ) . "'";
	}

	/**
	 * 
	 * @param string $dbType
	 * @return string
	 * 
	 * @deprecated use Database::getPHPType instead
	 */
	public function getPHPType(string $dbType): string {
		return '';
	}
	
	/**
	 * Returns the SQL string for a migration operation.
	 * @param string $operation
	 * @return string
	 */
	public function migrateOperation(string $operation):?string{
		return $this->operations[$operation]??null;
	}

	/**
	 * @param $isolationLevel
	 * @throws DBException
	 * @return mixed
	 */
	public function setIsolationLevel($isolationLevel){
		throw new DBException('The setIsolation level is not implemented for this wrapper');
	}
}
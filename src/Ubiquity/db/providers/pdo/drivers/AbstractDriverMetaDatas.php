<?php

namespace Ubiquity\db\providers\pdo\drivers;

/**
 * Ubiquity\db\providers$DriverMetaDatas
 * This class is part of Ubiquity
 *
 * @author jc
 * @version 1.0.1
 *
 */
abstract class AbstractDriverMetaDatas {
	protected $dbInstance;

	public function __construct($dbInstance) {
		$this->dbInstance = $dbInstance;
	}

	/**
	 * Returns all table names in the database.
	 *
	 * @return array
	 */
	abstract public function getTablesName(): array;

	/**
	 * Returns an array of the primary keys field names.
	 *
	 * @param string $tableName
	 * @return array
	 */
	abstract public function getPrimaryKeys(string $tableName): array;

	/**
	 * Returns the list of foreign keys in a table.
	 *
	 * @param string $tableName
	 * @param string $pkName
	 * @param string $dbName
	 * @return array
	 */
	abstract public function getForeignKeys(string $tableName, string $pkName, ?string $dbName = null): array;

	/**
	 * Returns metadata related to the fields of the specified table.
	 *
	 * @param string $tableName
	 * @return array
	 */
	abstract public function getFieldsInfos(string $tableName): array;

	/**
	 * Returns the line number of a data record.
	 *
	 * @param string $tableName
	 * @param string $pkName
	 * @param string $condition
	 * @return int
	 */
	abstract public function getRowNum(string $tableName, string $pkName, string $condition): int;

	/**
	 * Returns the SQL callback for fields concatenation
	 *
	 * @param string $fields
	 * @return string
	 */
	abstract public function groupConcat(string $fields, string $separator): string;

	public function toStringOperator() {
		return '';
	}
}


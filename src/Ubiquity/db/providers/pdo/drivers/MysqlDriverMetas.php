<?php

namespace Ubiquity\db\providers\pdo\drivers;

use Ubiquity\db\providers\DbOperations;

/**
 * Ubiquity\db\providers\pdo\drivers$MysqlDriverMetas
 * This class is part of Ubiquity
 *
 * @author jc
 * @version 1.0.3
 *
 */
class MysqlDriverMetas extends AbstractDriverMetaDatas {

	public function __construct($dbInstance) {
		parent::__construct($dbInstance);
		$this->operations[DbOperations::CREATE_TABLE]='CREATE TABLE {name} ({fields}) ENGINE=InnoDB DEFAULT CHARSET=utf8';
		$this->operations[DbOperations::AUTO_INC]='ALTER TABLE {tableName} MODIFY {fieldInfos} AUTO_INCREMENT, AUTO_INCREMENT={value}';
	}
	
	public function getTablesName(): array {
		$query = $this->dbInstance->query ( 'SHOW TABLES' );
		return $query->fetchAll ( \PDO::FETCH_COLUMN );
	}

	public function getPrimaryKeys(string $tableName): array {
		$fieldkeys = array ();
		$recordset = $this->dbInstance->query ( "SHOW KEYS FROM `{$tableName}` WHERE Key_name = 'PRIMARY'" );
		$keys = $recordset->fetchAll ( \PDO::FETCH_ASSOC );
		foreach ( $keys as $key ) {
			$fieldkeys [] = $key ['Column_name'];
		}
		return $fieldkeys;
	}

	public function getForeignKeys(string $tableName, string $pkName, ?string $dbName = null): array {
		$recordset = $this->dbInstance->query ( "SELECT *
												FROM
												 information_schema.KEY_COLUMN_USAGE
												WHERE
												 REFERENCED_TABLE_NAME = '" . $tableName . "'
												 AND REFERENCED_COLUMN_NAME = '" . $pkName . "'
												 AND TABLE_SCHEMA = '" . $dbName . "';" );
		return $recordset->fetchAll ( \PDO::FETCH_ASSOC );
	}

	public function getFieldsInfos(string $tableName): array {
		$fieldsInfos = array ();
		$recordset = $this->dbInstance->query ( "SHOW COLUMNS FROM `{$tableName}`" );
		$fields = $recordset->fetchAll ( \PDO::FETCH_ASSOC );
		foreach ( $fields as $field ) {
			$fieldsInfos [$field ['Field']] = [ "Type" => $field ['Type'],"Nullable" => $field ["Null"] ];
		}
		return $fieldsInfos;
	}

	public function getRowNum(string $tableName, string $pkName, string $condition): int {
		$query = $this->dbInstance->query ( "SELECT num FROM (SELECT *, @rownum:=@rownum + 1 AS num FROM `{$tableName}`, (SELECT @rownum:=0) r ORDER BY {$pkName}) d WHERE " . $condition );
		if ($query) {
			return $query->fetchColumn ( 0 );
		}
		return 0;
	}

	public function groupConcat(string $fields, string $separator): string {
		return "GROUP_CONCAT({$fields} SEPARATOR '{$separator}')";
	}

	public function setIsolationLevel($isolationLevel) {
		return $this->dbInstance->exec("SET TRANSACTION ISOLATION LEVEL $isolationLevel");
	}
}

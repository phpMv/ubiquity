<?php

namespace Ubiquity\db\providers\pdo\drivers;

class MysqlDriverMetas extends AbstractDriverMetaDatas {

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
}


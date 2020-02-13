<?php

namespace Ubiquity\db\providers\pdo\drivers;

/**
 * Ubiquity\db\providers\pdo\drivers$SqliteDriverMetas
 * This class is part of Ubiquity
 *
 * @author
 * @version 1.0.0
 *
 */
class SqliteDriverMetas extends AbstractDriverMetaDatas {

	public function getForeignKeys($tableName, $pkName, $dbName = null): array {

		// SQL lite may return error if key is not found.
		$recordset = $this->dbInstance->query ( "SELECT \"TABLE\" as TABLE_NAME , \"to\" as COLUMN_NAME , \"from\" as REFERENCED_TABLE_SCHEMA FROM pragma_foreign_key_list('".$tableName."') WHERE \"to\"='".$pkName."';");
		return $recordset->fetchAll ( \PDO::FETCH_ASSOC );
	}

	public function getTablesName(): array {
		$query = $this->dbInstance->query ( 'SELECT name FROM sqlite_master WHERE type=\'table\';'  );
		return $query->fetchAll ( \PDO::FETCH_COLUMN );
	}

	public function getPrimaryKeys($tableName): array {
		$fieldkeys = array ();
		$recordset = $this->dbInstance->query ( "PRAGMA TABLE_INFO(".$table_name.");" );
		$keys = $recordset->fetchAll ( \PDO::FETCH_ASSOC );
		foreach ( $keys as $key ) {
            if ($key['pk']==1) {
                $fieldkeys[] = $key['name'];
            }			
		}
		return $fieldkeys;
	}

	public function getFieldsInfos($tableName): array {
		$fieldsInfos = array ();
		$recordset = $this->dbInstance->query (  "PRAGMA TABLE_INFO(".$table_name.");" );
		$fields = $recordset->fetchAll ( \PDO::FETCH_ASSOC );
		foreach ( $fields as $field ) {
			$fieldsInfos [$field ['name']] = [ "Type" => $field ['type'],"Nullable" => $field ["notnull"] ];
		}
		return $fieldsInfos;
	}
}


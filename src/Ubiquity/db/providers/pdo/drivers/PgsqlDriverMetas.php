<?php

namespace Ubiquity\db\providers\pdo\drivers;

use Ubiquity\db\providers\DbOperations;

/**
 * Ubiquity\db\providers\pdo\drivers$PgsqlDriverMetas
 * This class is part of Ubiquity
 *
 * @author Ulaş SAYGIN
 * @version 1.0.3
 *
 */
class PgsqlDriverMetas extends AbstractDriverMetaDatas {

	public function __construct($dbInstance) {
		parent::__construct($dbInstance);
		$this->operations[DbOperations::AUTO_INC]='CREATE SEQUENCE {seqName};ALTER TABLE {tableName} ALTER COLUMN {fieldName} SET DEFAULT nextval({seqName});';
		$this->operations[DbOperations::MODIFY_FIELD]='ALTER TABLE {tableName} ALTER COLUMN {fieldName} TYPE {attributes}';
	}

	public function getForeignKeys($tableName, $pkName, $dbName = null): array {
		$recordset = $this->dbInstance->query ( 'SELECT k1.constraint_catalog as "CONSTRAINT_CATALOG", k1.constraint_schema as "CONSTRAINT_SCHEMA",
												k1.constraint_name as "CONSTRAINT_NAME",
												k1.table_catalog  as "TABLE_CATALOG",
												k1.table_schema  as "TABLE_SCHEMA",
												k1.table_name  as "TABLE_NAME",
												k1.column_name as "COLUMN_NAME",
												k1.ordinal_position as "ORDINAL_POSITION" ,
												k1.position_in_unique_constraint as "POSITION_IN_UNIQUE_CONSTRAINT",
												k2.table_schema AS "REFERENCED_TABLE_SCHEMA",
												k2.table_name AS "REFERENCED_TABLE_NAME",
												k2.column_name AS "REFERENCED_COLUMN_NAME"
												FROM information_schema.key_column_usage k1
													JOIN information_schema.referential_constraints fk USING (constraint_schema, constraint_name)
													JOIN information_schema.key_column_usage k2
													ON k2.constraint_schema = fk.unique_constraint_schema
												AND k2.constraint_name = fk.unique_constraint_name
												AND k2.ordinal_position = k1.position_in_unique_constraint
												WHERE k1.table_schema = \'public\'
												and k2.column_name=\'' . $pkName . '\'
												AND k2.table_name   = \'' . $tableName . '\';' );
		return $recordset->fetchAll ( \PDO::FETCH_ASSOC );
	}

	public function getTablesName(): array {
		$query = $this->dbInstance->query ( 'SELECT tablename as schemaname FROM pg_catalog.pg_tables WHERE schemaname != \'pg_catalog\' AND schemaname != \'information_schema\';' );
		return $query->fetchAll ( \PDO::FETCH_COLUMN );
	}

	public function getPrimaryKeys($tableName): array {
		$fieldkeys = array ();
		$recordset = $this->dbInstance->query ( "SELECT a.attname, format_type(a.atttypid, a.atttypmod) AS data_type FROM pg_index i JOIN pg_attribute a ON a.attrelid = i.indrelid AND a.attnum = ANY(i.indkey) WHERE i.indrelid = '\"{$tableName}\"'::regclass AND i.indisprimary;" );
		$keys = $recordset->fetchAll ( \PDO::FETCH_ASSOC );
		foreach ( $keys as $key ) {
			$fieldkeys [] = $key ['attname'];
		}
		return $fieldkeys;
	}

	public function getFieldsInfos($tableName): array {
		$fieldsInfos = array ();
		$recordset = $this->dbInstance->query ( "SELECT
			f.attname AS \"Field\",
			pg_catalog.format_type(f.atttypid,f.atttypmod) AS \"Type\",
			CASE
			    WHEN f.attnotnull=true THEN 'YES'
			    WHEN f.attnotnull=false THEN 'NO'
			    ELSE ''
			END AS \"Null\",
			CASE
			    WHEN p.contype = 'u' THEN 'MUL'
			    WHEN p.contype = 'p' THEN 'PRI'
			    ELSE ''
			END AS \"Key\",
			CASE
			    WHEN f.atthasdef = 't' THEN pg_get_expr(adbin, adrelid)
			END AS \"Default\"  ,
			CASE WHEN pg_get_expr(adbin, adrelid) LIKE 'nextval(%' THEN 'auto_increment' ELSE '' END AS \"Extra\"
			FROM pg_attribute f
			JOIN pg_class c ON c.oid = f.attrelid
			JOIN pg_type t ON t.oid = f.atttypid
			LEFT JOIN pg_attrdef d ON d.adrelid = c.oid AND d.adnum = f.attnum
			LEFT JOIN pg_namespace n ON n.oid = c.relnamespace
			LEFT JOIN pg_constraint p ON p.conrelid = c.oid AND f.attnum = ANY (p.conkey)
			LEFT JOIN pg_class AS g ON p.confrelid = g.oid
			LEFT JOIN pg_index AS ix ON f.attnum = ANY(ix.indkey) and c.oid = f.attrelid and c.oid = ix.indrelid
			LEFT JOIN pg_class AS i ON ix.indexrelid = i.oid

			WHERE c.relkind = 'r'::char
			AND n.nspname = 'public'
			and c.relname='{$tableName}'
			AND f.attnum > 0
			ORDER BY f.attnum;" );
		$fields = $recordset->fetchAll ( \PDO::FETCH_ASSOC );
		foreach ( $fields as $field ) {
			$fieldsInfos [$field ['Field']] = [ "Type" => $field ['Type'],"Nullable" => $field ["Null"] ];
		}
		return $fieldsInfos;
	}

	public function getRowNum(string $tableName, string $pkName, string $condition): int {
		$query = $this->dbInstance->query ( "SELECT num FROM (SELECT *,row_number() OVER (ORDER BY {$pkName}) AS num FROM \"{$tableName}\") x where " . $condition );
		if ($query) {
			return $query->fetchColumn ( 0 );
		}
		return 0;
	}

	public function groupConcat(string $fields, string $separator): string {
		return "array_to_string(array_agg({$fields}), '{$separator}')";
	}

	public function toStringOperator() {
		return '::TEXT ';
	}
}

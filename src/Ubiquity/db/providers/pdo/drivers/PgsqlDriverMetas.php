<?php

namespace Ubiquity\db\providers\pdo\drivers;

/**
 * Ubiquity\db\providers\pdo\drivers$PgsqlDriverMetas
 * This class is part of Ubiquity
 *
 * @author
 * @version 1.0.0
 *
 */
class PgsqlDriverMetas extends AbstractDriverMetaDatas {

	public function getForeignKeys($tableName, $pkName, $dbName = null): array {
	}

	public function getTablesName(): array {
	}

	public function getPrimaryKeys($tableName): array {
	}

	public function getFieldsInfos($tableName): array {
	}
}


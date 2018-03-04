<?php

namespace Ubiquity\db\export;

use Ubiquity\orm\DAO;

class SqlExport extends DataExport {

	public function __construct($batchSize=5) {
		parent::__construct($batchSize);
	}

	public function exports($tableName, $fields, $condition="") {
		$datas=DAO::$db->prepareAndExecute($tableName, $condition, $fields, false);
		return $this->generateInsert($tableName, $fields, $datas);
	}

	protected function batchOneRow($row, $fields) {
		$result=[ ];
		foreach ( $fields as $field ) {
			$result[]="'" . $row[$field] . "'";
		}
		return \implode(",", $result);
	}
}

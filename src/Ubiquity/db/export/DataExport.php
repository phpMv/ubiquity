<?php

namespace Ubiquity\db\export;

use Ubiquity\db\SqlUtils;

class DataExport {
	protected $batchSize;

	public function __construct($batchSize=20) {
		$this->batchSize=$batchSize;
	}

	protected function generateInsert($table, $fields, $datas) {
		$result=[ ];
		$batchRows=[ ];
		$rows=[ ];
		$batch=0;
		foreach ( $datas as $row ) {
			if ($batch < $this->batchSize) {
				$rows[]=$row;
				$batch++;
			} else {
				$batchRows[]=$this->batchRows($rows, $fields);
				$batch=0;
				$rows=[ ];
			}
		}
		if (\sizeof($rows) > 0) {
			$batchRows[]=$this->batchRows($rows, $fields);
		}
		foreach ( $batchRows as $batchRow ) {
			$result[]=$this->generateOneInsert($table, $fields, $batchRow);
		}
		return \implode(";\n", $result) . ";";
	}

	protected function generateOneInsert($table, $fields, $datas) {
		return "INSERT INTO `" . $table . "` (" . SqlUtils::getFieldList($fields) . ") VALUES " . $datas;
	}

	protected function batchRows($rows, $fields) {
		$result=[ ];
		foreach ( $rows as $row ) {
			$result[]="(" . $this->batchOneRow($row, $fields) . ")";
		}
		return \implode(",", $result);
	}

	protected function batchOneRow($row, $fields) {
		$result=[ ];
		foreach ( $fields as $field ) {
			$result[]="'" . $row->_rest[$field] . "'";
		}
		return \implode(",", $result);
	}
}

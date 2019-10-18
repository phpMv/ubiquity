<?php

namespace Ubiquity\orm\bulk;

use Ubiquity\orm\OrmUtils;
use Ubiquity\db\SqlUtils;

/**
 * Ubiquity\orm\bulk$BulkInserts
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
class BulkInserts extends AbstractBulks {
	protected $insertFields;

	private function getQuotedKeys($fields, $quote) {
		$ret = array ();
		foreach ( $fields as $field ) {
			$ret [] = $quote . $field . $quote;
		}
		return $ret;
	}

	public function __construct($className) {
		parent::__construct ( $className );
		$this->insertFields = \implode ( ',', $this->getQuotedKeys ( $this->fields, $this->db->quote ) );
	}

	public function addInstance($instance, $id = null) {
		$this->updateInstanceRest ( $instance );
		$this->instances [] = $instance;
	}

	public function createSQL() {
		$quote = $this->db->quote;
		$tableName = OrmUtils::getTableName ( $this->class );
		$fieldCount = \count ( $this->fields );
		$parameters = [ ];
		$values = [ ];
		$modelFields = '(' . \implode ( ',', \array_fill ( 0, $fieldCount, '?' ) ) . ')';
		foreach ( $this->instances as $instance ) {
			$parameters += $instance->_rest;
			$values [] = $modelFields;
		}
		$this->parameters = $parameters;
		return "INSERT INTO {$quote}{$tableName}{$quote} (" . $this->insertFields . ') VALUES ' . \implode ( ',', $values );
	}
}


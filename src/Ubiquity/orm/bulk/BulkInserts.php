<?php

namespace Ubiquity\orm\bulk;

use Ubiquity\orm\OrmUtils;

/**
 * Ubiquity\orm\bulk$BulkInserts
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
class BulkInserts extends AbstractBulks {

	public function __construct($className) {
		parent::__construct ( $className );
		if (($key = \array_search ( $this->pkName, $this->fields )) !== false) {
			unset ( $this->fields [$key] );
		}
		$this->insertFields = \implode ( ',', $this->getQuotedKeys ( $this->fields, $this->db->quote ) );
	}

	public function addInstance($instance, $id = null) {
		$this->updateInstanceRest ( $instance );
		unset ( $instance->_rest [$this->pkName] );
		$this->instances [] = $instance;
	}

	public function createSQL() {
		$quote = $this->db->quote;
		$fieldCount = \count ( $this->fields );
		$parameters = [ ];
		$values = [ ];
		$modelFields = '(' . \implode ( ',', \array_fill ( 0, $fieldCount, '?' ) ) . ')';
		foreach ( $this->instances as $instance ) {
			$parameters = \array_merge ( $parameters, \array_values ( $instance->_rest ) );
			$values [] = $modelFields;
		}
		$this->parameters = $parameters;
		return "INSERT INTO {$quote}{$this->tableName}{$quote} (" . $this->insertFields . ') VALUES ' . \implode ( ',', $values );
	}
}


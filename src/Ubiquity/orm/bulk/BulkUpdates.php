<?php

namespace Ubiquity\orm\bulk;

use Ubiquity\orm\OrmUtils;

/**
 * Ubiquity\orm\bulk$BulkUpdates
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
class BulkUpdates extends AbstractBulks {

	public function __construct($className) {
		parent::__construct ( $className );
		$this->insertFields = \implode ( ',', $this->getQuotedKeys ( $this->fields, $this->db->quote ) );
	}

	public function addInstance($instance, $id = null) {
		$id = $id ?? OrmUtils::getFirstKeyValue ( $instance );
		$this->updateInstanceRest ( $instance );
		$this->instances [$id] = $instance;
	}

	public function createSQL() {
		switch ($this->dbType) {
			case 'mysql' :
				return $this->mysqlCreate ();
			case 'pgsql' :
				return $this->pgCreate ();
			default :
				throw new \RuntimeException ( $this->dbType . ' does not support bulk updates!' );
		}
	}

	private function pgCreate() {
		$quote = $this->db->quote;
		$tableName = OrmUtils::getTableName ( $this->class );

		$count = \count ( $this->instances );
		$modelField = \implode ( '', \array_fill ( 0, $count, ' WHEN ? THEN ? ' ) );

		$keys = \array_keys ( $this->instances );
		$parameters = [ ];
		$_rest = [ ];
		foreach ( $this->instances as $k => $instance ) {
			$_rest [$k] = $instance->_rest;
		}

		$caseFields = [ ];
		$pk = $this->pkName;
		foreach ( $this->fields as $field ) {
			$caseFields [] = "{$quote}{$field}{$quote} = (CASE {$quote}{$pk}{$quote} {$modelField} ELSE {$quote}{$field}{$quote} END)";
			foreach ( $_rest as $pkv => $_restInstance ) {
				$parameters [] = $pkv;
				$parameters [] = $_restInstance [$field];
			}
		}
		$parameters = \array_merge ( $parameters, $keys );
		$this->parameters = $parameters;
		return "UPDATE {$quote}{$tableName}{$quote} SET " . \implode ( ',', $caseFields ) . " WHERE {$quote}{$pk}{$quote} IN (" . \implode ( ',', \array_fill ( 0, $count, '?' ) ) . ')';
	}

	private function mysqlCreate() {
		$quote = $this->db->quote;
		$tableName = OrmUtils::getTableName ( $this->class );
		$fieldCount = \count ( $this->fields );
		$parameters = [ ];
		$values = [ ];
		$modelFields = '(' . \implode ( ',', \array_fill ( 0, $fieldCount, '?' ) ) . ')';
		foreach ( $this->instances as $instance ) {
			$parameters = \array_merge ( $parameters, \array_values ( $instance->_rest ) );
			$values [] = $modelFields;
		}
		$duplicateKey = [ ];
		foreach ( $this->fields as $field ) {
			$duplicateKey [] = "{$quote}{$field}{$quote} = VALUES({$quote}{$field}{$quote})";
		}
		$this->parameters = $parameters;
		return "INSERT INTO {$quote}{$tableName}{$quote} (" . $this->insertFields . ') VALUES ' . \implode ( ',', $values ) . ' ON DUPLICATE KEY UPDATE ' . \implode ( ',', $duplicateKey );
	}
}


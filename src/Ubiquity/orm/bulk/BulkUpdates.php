<?php

namespace Ubiquity\orm\bulk;

use Ubiquity\orm\OrmUtils;

/**
 * Ubiquity\orm\bulk$BulkUpdates
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.3
 *
 */
class BulkUpdates extends AbstractBulks {
	private $sqlUpdate = [ ];

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
			case 'pgsql' :
			case 'tarantool' :
				return $this->pgCreate ();
			default :
				throw new \RuntimeException ( $this->dbType . ' does not support bulk updates!' );
		}
	}

	private function pgCreate() {
		$quote = $this->db->quote;

		$count = \count ( $this->instances );
		$modelField = \str_repeat ( ' WHEN ? THEN ? ', $count );

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
		$parameters = [ ...$parameters,...$keys ];
		$this->parameters = $parameters;
		return "UPDATE {$quote}{$this->tableName}{$quote} SET " . \implode ( ',', $caseFields ) . " WHERE {$quote}{$pk}{$quote} IN (" . \str_repeat ( '?,', $count - 1 ) . '?)';
	}

	private function mysqlCreate() {
		$quote = $this->db->quote;
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
		return "INSERT INTO {$quote}{$this->tableName}{$quote} (" . $this->insertFields . ') VALUES ' . \implode ( ',', $values ) . ' ON DUPLICATE KEY UPDATE ' . \implode ( ',', $duplicateKey );
	}

	private function getUpdateFields() {
		$ret = array ();
		$quote = $this->db->quote;
		foreach ( $this->insertFields as $field ) {
			$ret [] = $quote . $field . $quote . '= :' . $field;
		}
		return \implode ( ',', $ret );
	}

	public function updateGroup($count = 5) {
		$quote = $this->db->quote;
		$groups = \array_chunk ( $this->instances, $count );

		foreach ( $groups as $group ) {
			$sql = '';
			foreach ( $group as $instance ) {
				$kv = OrmUtils::getKeyFieldsAndValues ( $instance );
				$sql .= "UPDATE {$quote}{$this->tableName}{$quote} SET " . $this->db->getUpdateFieldsKeyAndValues ( $instance->_rest ) . ' WHERE ' . $this->db->getCondition ( $kv ) . ';';
			}
			$this->execGroupTrans ( $sql );
		}
		$this->instances = [ ];
		$this->parameters = [ ];
	}
}


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

	public function addInstance($instance, $id = null) {
		$id = $id ?? OrmUtils::getFirstKeyValue ( $instance );
		$this->updateInstanceRest ( $instance );
		$this->instances [$id] = $instance;
	}

	public function createSQL() {
		$quote = $this->db->quote;
		$tableName = OrmUtils::getTableName ( $this->class );

		$count = \count ( $this->instances );
		$modelField = implode ( '', \array_fill ( 0, $count, ' WHEN ? THEN ? ' ) );

		$keys = \array_keys ( $this->instances );
		$parameters = [ ];
		$_rest = [ ];
		foreach ( $this->instances as $k => $instance ) {
			$_rest [$k] = $instance->_rest;
		}

		$caseFields = [ ];
		$pk = $this->pkName;
		foreach ( $this->fields as $field ) {
			$caseFields [] = "{$quote}{$field}{$quote} = CASE {$quote}{$pk}{$quote} {$modelField} ELSE {$quote}{$field}{$quote} END";
			foreach ( $_rest as $pkv => $_restInstance ) {
				$parameters [] = $pkv;
				$parameters [] = $_restInstance [$field];
			}
		}
		$parameters = \array_merge ( $parameters, $keys );
		$this->parameters = $parameters;
		return "UPDATE {$quote}{$tableName}{$quote} SET " . \implode ( ',', $caseFields ) . " WHERE {$quote}{$pk}{$quote} IN (" . \implode ( ',', \array_fill ( 0, $count, '?' ) ) . ")";
	}
}


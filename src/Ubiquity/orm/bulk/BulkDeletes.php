<?php

namespace Ubiquity\orm\bulk;

use Ubiquity\orm\OrmUtils;

/**
 * Ubiquity\orm\bulk$BulkDeletes
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
class BulkDeletes extends AbstractBulks {

	public function addInstance($instance, $id = null) {
		$id = $id ?? OrmUtils::getFirstKeyValue ( $instance );
		$this->instances [$id] = $instance;
	}

	public function createSQL() {
		$quote = $this->db->quote;
		$this->parameters = \array_keys ( $this->instances );
		$count = \count ( $this->parameters );

		return "DELETE FROM {$quote}{$this->tableName}{$quote} WHERE {$quote}{$this->pkName}{$quote} IN (" . \implode ( ',', \array_fill ( 0, $count, '?' ) ) . ')';
	}
}


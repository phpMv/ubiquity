<?php

namespace Ubiquity\orm\bulk;

use Ubiquity\orm\OrmUtils;
use Ubiquity\orm\DAO;
use Ubiquity\log\Logger;

/**
 * Ubiquity\orm\bulk$AbstractBulks
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
abstract class AbstractBulks {
	protected $class;
	protected $pkName;
	protected $fields;
	protected $db;
	protected $instances = [ ];
	protected $parameters;

	protected function updateInstanceRest($instance) {
		foreach ( $this->fields as $field ) {
			$accessor = "get" . \ucfirst ( $field );
			$instance->_rest [$field] = $instance->$accessor ();
		}
	}

	public function __construct($className) {
		$this->class = $className;
		$this->pkName = OrmUtils::getFirstKey ( $className );
		$this->fields = OrmUtils::getSerializableFields ( $className );
		$this->db = DAO::getDb ( $className );
	}

	public function addInstances($instances) {
		foreach ( $instances as $instance ) {
			$this->addInstance ( $instance );
		}
	}

	public abstract function addInstance($instance);

	public abstract function createSQL();

	public function flush() {
		$statement = $this->db->getUpdateStatement ( $this->createSQL () );
		try {
			$result = $statement->execute ( $this->parameters );
			$this->instances = [ ];
			$this->parameters = [ ];
			return $result;
		} catch ( \Exception $e ) {
			Logger::warn ( "DAOBulkUpdates", $e->getMessage (), \get_class ( $this ) );
		}
		return false;
	}
}


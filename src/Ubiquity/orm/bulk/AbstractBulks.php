<?php

namespace Ubiquity\orm\bulk;

use Ubiquity\orm\OrmUtils;
use Ubiquity\orm\DAO;
use Ubiquity\log\Logger;
use Ubiquity\controllers\Startup;

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
	protected $tableName;
	protected $db;
	protected $instances = [ ];
	protected $parameters;
	protected $dbType;
	protected $insertFields;

	protected function getQuotedKeys($fields, $quote) {
		$ret = array ();
		foreach ( $fields as $field ) {
			$ret [] = $quote . $field . $quote;
		}
		return $ret;
	}

	protected function updateInstanceRest($instance) {
		foreach ( $this->fields as $field ) {
			$accessor = 'get' . \ucfirst ( $field );
			$instance->_rest [$field] = $instance->$accessor ();
		}
	}

	protected function execGroupTrans($sql) {
		while ( true ) {
			try {
				$this->db->beginTransaction ();
				$this->db->execute ( $sql );
				$this->db->commit ();
				return true;
			} catch ( \Exception $e ) {
				$this->db->rollBack ();
			}
		}
		return false;
	}

	public function __construct($className) {
		$this->class = $className;
		$this->pkName = OrmUtils::getFirstKey ( $className );
		$this->fields = OrmUtils::getSerializableFields ( $className );
		$this->db = DAO::getDb ( $className );
		$this->dbType = $this->db->getDbType ();
		$this->tableName = OrmUtils::getTableName ( $className );
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
		while ( true ) {
			try {
				$result = $statement->execute ( $this->parameters );
				if ($result !== false) {
					$this->instances = [ ];
					$this->parameters = [ ];
					return $result;
				}
			} catch ( \Exception $e ) {
				Logger::warn ( "DAOBulkUpdates", $e->getMessage (), \get_class ( $this ) );
				if ($e->errorInfo [0] == 40001 && $this->db->getDbType () == 'mysql' && $e->errorInfo [1] == 1213) {
					echo "deadlock";
				} else {
					if (Startup::$config ['debug']) {
						throw $e;
					}
				}
			}
		}
		return false;
	}
}


<?php

namespace Ubiquity\orm\reverse;

use Ubiquity\db\reverse\DbGenerator;
use Ubiquity\controllers\Startup;
use Ubiquity\cache\CacheManager;

/**
 * Generates database from models.
 * Ubiquity\orm\reverse$DatabaseReversor
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.1
 *
 */
class DatabaseReversor {
	private $generator;
	private $database;

	public function __construct(DbGenerator $generator, $databaseOffset = 'default') {
		$this->generator = $generator;
		$this->database = $databaseOffset;
	}

	public function createDatabase($name) {
		$this->generator->createDatabase ( $name );
		$this->generator->selectDatabase ( $name );
		$config = Startup::getConfig ();
		$models = CacheManager::getModels ( $config, true, $this->database );
		foreach ( $models as $model ) {
			$tableReversor = new TableReversor ( $model );
			$tableReversor->initFromClass ();
			$tableReversor->generateSQL ( $this->generator );
		}
		$this->generator->generateManyToManys ();
	}

	public function __toString() {
		return $this->generator->__toString ();
	}
}

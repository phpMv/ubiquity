<?php

namespace Ubiquity\orm\reverse;

use Ubiquity\db\reverse\DbGenerator;
use Ubiquity\controllers\Startup;
use Ubiquity\cache\CacheManager;

class DatabaseReversor {
	private $generator;
	public function __construct(DbGenerator $generator){
		$this->generator=$generator;
	}

	public function createDatabase($name){
		$this->generator->createDatabase($name);
		$this->generator->selectDatabase($name);
		$config=Startup::getConfig();
		$models=CacheManager::getModels($config,true);
		foreach ($models as $model){
			$tableReversor=new TableReversor($model);
			$tableReversor->initFromClass();
			$tableReversor->generateSQL($this->generator);
		}
		$this->generator->generateManyToManys();
	}

	public function __toString(){
		return $this->generator->__toString();
	}
}

<?php

namespace Ubiquity\db\export;

use Ubiquity\controllers\Startup;
use Ubiquity\cache\CacheManager;
use Ubiquity\orm\parser\ManyToManyParser;
use Ubiquity\db\SqlUtils;

/**
 * Exports existing models to SQL
 * Ubiquity\db\export$DbExport
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.1
 *
 */
class DbExport {
	protected $manyToManys = [ ];

	public function __construct() {
	}

	public function exports() {
		$result = [ ];
		$config = Startup::getConfig ();
		$models = CacheManager::getModels ( $config, true );
		foreach ( $models as $model ) {
			$tableExport = new TableExport ( $model );
			$result [] = $tableExport->exports ( $this );
		}
		foreach ( $this->manyToManys as $target => $ManyToManyParser ) {
			$ManyToManyParser->init ();
			$sqlExport = new SqlExport ();
			$members = [ $ManyToManyParser->getFkField (),$ManyToManyParser->getMyFkField () ];
			$fields = SqlUtils::getFieldList ( $members, $target );
			$result [] = $sqlExport->exports ( $target, $fields );
		}
		return \implode ( "\n", $result );
	}

	public function addManyToMany($jointable, $memberTargetEntity) {
		if (! isset ( $this->manyToManys [$jointable] )) {
			$this->manyToManys [$jointable] = new ManyToManyParser ( $memberTargetEntity ["class"], $memberTargetEntity ["member"] );
		}
	}
}

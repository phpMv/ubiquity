<?php

namespace Ubiquity\controllers\rest\formatters;

use Ubiquity\orm\OrmUtils;
use Ubiquity\utils\http\URequest;

/**
 * Ubiquity\controllers\rest\formatters$JsonRequestFormatter
 * This class is part of Ubiquity
 *
 * @author jc
 * @version 1.0.0
 *
 */
class JsonRequestFormatter extends RequestFormatter {

	public function getDatas(?string $model = null): array {
		$datas = URequest::getRealInput ();
		if (\count ( $datas ) > 0) {
			$datas = \current ( \array_keys ( $datas ) );
			return $this->updateDatasForRelationships ( \json_decode ( $datas, true ), $model );
		}
		return [ ];
	}

	protected function updateDatasForRelationships($datas, $model) {
		$metas = OrmUtils::getModelMetadata ( $model );
		$joinColumns = $metas ['#joinColumn'] ?? [ ];
		$manyToManys = $metas ['#manyToMany'] ?? [ ];
		foreach ( $joinColumns as $column => $infos ) {
			if(isset($datas [$column])) {
				$datas [$infos ['name']] = $datas [$column];
				unset ($datas [$column]);
			}
		}
		foreach ( $manyToManys as $manyColumn => $manyColumnInfos ) {
			$targetEntity = $manyColumnInfos ['targetEntity'];
			$idField = OrmUtils::getFirstKey ( $targetEntity );
			$v = $datas [$manyColumn] ?? [];
			$ids = [ ];
			foreach ( $v as $values ) {
				if (isset ( $values [$idField] )) {
					$ids [] = $values [$idField];
				}
			}
			$datas [$manyColumn . 'Ids'] = \implode ( ',', $ids );
			unset ( $datas [$manyColumn] );
		}
		return $datas;
	}
}
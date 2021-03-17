<?php

namespace Ubiquity\orm\core;

class PendingRelationsRequest {
	public static $MAX_ROW_COUNT = 35;
	/**
	 *
	 * @var ObjectsConditionParser[]
	 */
	protected $objectsConditionParsers = [ ];
	/**
	 *
	 * @var ObjectsConditionParser
	 */
	protected $activeObjectsParser;

	public function __construct() {
		$this->addNewParser ();
	}

	public function addPartObject($object, $condition, $value) {
		$inserted = false;
		$i = 0;
		$count = \count ( $this->objectsConditionParsers );
		while ( ! $inserted && $i < $count ) {
			$objectsConditionParser = $this->objectsConditionParsers [$i];
			if ($objectsConditionParser->hasParam ( $value )) {
				$objectsConditionParser->addObject ( $object );
				$inserted = true;
			}
			$i ++;
		}
		if (! $inserted) {
			$this->getActiveParser ()->addPartObject ( $object, $condition, $value );
		}
	}

	protected function addNewParser(): ObjectsConditionParser {
		$this->activeObjectsParser = new ObjectsConditionParser ();
		return $this->objectsConditionParsers [] = $this->activeObjectsParser;
	}

	protected function getActiveParser(): ObjectsConditionParser {
		if ($this->activeObjectsParser->isFull ()) {
			return $this->addNewParser ();
		}
		return $this->activeObjectsParser;
	}

	/**
	 *
	 * @return \Ubiquity\orm\core\ObjectsConditionParser[]
	 */
	public function getObjectsConditionParsers(): array {
		return $this->objectsConditionParsers;
	}
}


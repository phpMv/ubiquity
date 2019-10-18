<?php

namespace Ubiquity\orm\traits;

use Ubiquity\orm\bulk\BulkUpdates;

/**
 * Ubiquity\orm\traits$DAOBulkUpdatesTrait
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
trait DAOBulkUpdatesTrait {
	protected static $bulks = [ ];

	protected static function getBulk($instance, $class, $operation = 'update') {
		self::bulks [$operation] = self::bulks [$operation] ?? [ ];
		if (! isset ( self::bulks [$operation] [$class] )) {
			self::bulks [$operation] [$class] = new BulkUpdates ( $class );
		}
		return self::bulks [$operation] [$class];
	}

	protected static function toOperation($instance, string $operation): void {
		$class = \get_class ( $instance );
		self::getBulk ( $instance, $class, $operation )->addInstance ( $instance );
	}

	protected static function toOperations(array $instances, string $operation): void {
		$instance = \current ( $instances );
		if (isset ( $instance )) {
			$class = \get_class ( $instance );
			self::getBulk ( $instance, $class, $operation )->addInstances ( $instances );
		}
	}

	public function toUpdate(object $instance): void {
		self::toOperation ( $instance, 'update' );
	}

	public function toUpdates(array $instances): void {
		self::toOperations ( $instances, 'update' );
	}

	public static function flushUpdates(): void {
		$bulks = self::$bulks ['update'];
		foreach ( $bulks as $bulk ) {
			$bulk->flush ();
		}
	}

	public static function flush(): void {
		foreach ( self::$bulks as $bulks ) {
			foreach ( $bulks as $bulk ) {
				$bulk->flush ();
			}
		}
	}
}


<?php

namespace Ubiquity\orm\traits;

/**
 * Ubiquity\orm\traits$DAOBulkUpdatesTrait
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
trait DAOBulkUpdatesTrait {
	protected static $bulks = [ 'insert' => [ ],'update' => [ ],'delete' => [ ] ];

	protected static function getBulk($instance, $class, $operation = 'update') {
		if (! isset ( self::$bulks [$operation] [$class] )) {
			$bulkClass = '\\Ubiquity\\orm\\bulk\\Bulk' . \ucfirst ( $operation ) . 's';
			self::$bulks [$operation] [$class] = new $bulkClass ( $class );
		}
		return self::$bulks [$operation] [$class];
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

	/**
	 * Adds an instance in the bulk list of objects to insert.
	 * Call flush to commit the operation
	 *
	 * @param object $instance
	 */
	public static function toInsert(object $instance): void {
		self::toOperation ( $instance, 'insert' );
	}

	public static function toInserts(array $instances): void {
		self::toOperations ( $instances, 'insert' );
	}

	public static function flushInserts(): void {
		$bulks = self::$bulks ['insert'];
		foreach ( $bulks as $bulk ) {
			$bulk->flush ();
		}
	}

	public static function toUpdate(object $instance): void {
		self::toOperation ( $instance, 'update' );
	}

	public static function toUpdates(array $instances): void {
		self::toOperations ( $instances, 'update' );
	}

	public static function flushUpdates(): void {
		$bulks = self::$bulks ['update'];
		foreach ( $bulks as $bulk ) {
			$bulk->flush ();
		}
	}

	public static function toDelete(object $instance): void {
		self::toOperation ( $instance, 'delete' );
	}

	public static function toDeletes(array $instances): void {
		self::toOperations ( $instances, 'delete' );
	}

	public static function flushDeletes(): void {
		$bulks = self::$bulks ['delete'];
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


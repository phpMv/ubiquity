<?php

namespace Ubiquity\orm\traits;

/**
 * Ubiquity\orm\traits$DAOBulkUpdatesTrait
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.4
 *
 */
trait DAOBulkUpdatesTrait {
	protected static $bulks = [ 'insert' => [ ],'update' => [ ],'delete' => [ ] ];

	protected static function getBulk($class, $operation = 'update') {
		if (! isset ( self::$bulks [$operation] [$class] )) {
			$bulkClass = '\\Ubiquity\\orm\\bulk\\Bulk' . \ucfirst ( $operation ) . 's';
			self::$bulks [$operation] [$class] = new $bulkClass ( $class );
		}
		return self::$bulks [$operation] [$class];
	}

	protected static function toOperation($instance, string $operation): void {
		$class = \get_class ( $instance );
		self::getBulk ( $class, $operation )->addInstance ( $instance );
	}

	protected static function toOperations(array $instances, string $operation): void {
		$instance = \current ( $instances );
		if (isset ( $instance )) {
			$class = \get_class ( $instance );
			self::getBulk ( $class, $operation )->addInstances ( $instances );
		}
	}

	/**
	 * Adds an instance in the bulk list of objects to insert.
	 * Call flush to commit the operation
	 *
	 * @param object $instance
	 */
	public static function toInsert($instance): void {
		self::toOperation ( $instance, 'insert' );
	}

	/**
	 * Adds an array of instances in the bulk list of objects to insert.
	 * Call flush to commit the operation
	 *
	 * @param array $instances
	 */
	public static function toInserts(array $instances): void {
		self::toOperations ( $instances, 'insert' );
	}

	/**
	 * Executes all waiting insert operations
	 */
	public static function flushInserts(): void {
		$bulks = self::$bulks ['insert'];
		foreach ( $bulks as $bulk ) {
			$bulk->flush ();
		}
	}

	/**
	 * Adds an instance in the bulk list of objects to update.
	 * Call flush to commit the operation
	 *
	 * @param object $instance
	 */
	public static function toUpdate($instance): void {
		self::toOperation ( $instance, 'update' );
	}

	/**
	 * Adds an array of instances in the bulk list of objects to update.
	 * Call flush to commit the operation
	 *
	 * @param array $instances
	 */
	public static function toUpdates(array $instances): void {
		self::toOperations ( $instances, 'update' );
	}

	public static function updateGroups($count = 5) {
		$bulks = self::$bulks ['update'];
		foreach ( $bulks as $bulk ) {
			$bulk->groupOp ( $count );
		}
	}

	public static function insertGroups($count = 5) {
		$bulks = self::$bulks ['insert'];
		foreach ( $bulks as $bulk ) {
			$bulk->groupOp ( $count );
		}
	}

	public static function deleteGroups($count = 5) {
		$bulks = self::$bulks ['delete'];
		foreach ( $bulks as $bulk ) {
			$bulk->groupOp ( $count );
		}
	}

	/**
	 * Executes all waiting update operations
	 */
	public static function flushUpdates(): void {
		$bulks = self::$bulks ['update'];
		foreach ( $bulks as $bulk ) {
			$bulk->flush ();
		}
	}

	/**
	 * Adds an instance in the bulk list of objects to delete.
	 * Call flush to commit the operation
	 *
	 * @param object $instance
	 */
	public static function toDelete($instance): void {
		self::toOperation ( $instance, 'delete' );
	}

	/**
	 * Adds an array of instances in the bulk list of objects to delete.
	 * Call flush to commit the operation
	 *
	 * @param array $instances
	 */
	public static function toDeletes(array $instances): void {
		self::toOperations ( $instances, 'delete' );
	}

	/**
	 * Executes all waiting delete operations
	 */
	public static function flushDeletes(): void {
		$bulks = self::$bulks ['delete'];
		foreach ( $bulks as $bulk ) {
			$bulk->flush ();
		}
	}

	/**
	 * Executes all waiting operations (inserts, updates, deletes)
	 */
	public static function flush(): void {
		foreach ( self::$bulks as $bulks ) {
			foreach ( $bulks as $bulk ) {
				$bulk->flush ();
			}
		}
	}

	/**
	 * Clear bulk and clear instances waiting for operations.
	 *
	 * @param array $operations
	 * @param array $classes
	 */
	public static function clearBulks(?array $operations = null, ?array $classes = null): void {
		$operations ??= \array_keys ( self::$bulks );
		foreach ( $operations as $op ) {
			$thisClasses = $classes ?? \array_keys ( self::$bulks [$op] );
			foreach ( $thisClasses as $class ) {
				if (isset ( self::$bulks [$op] [$class] )) {
					self::$bulks [$op] [$class]->clear ();
				}
			}
		}
	}

	/**
	 * Return the count of instances waiting for flushing in a bulk.
	 *
	 * @param string $class
	 * @param string $operation
	 * @return int
	 */
	public static function countInstancesBulk(string $class, string $operation = 'update'): int {
		$bulk = self::$bulks [$operation] [$class] ?? null;
		if (isset ( $bulk )) {
			return $bulk->count ();
		}
		return 0;
	}
}


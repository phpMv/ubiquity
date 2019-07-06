<?php

namespace Ubiquity\events;

use Ubiquity\cache\CacheManager;
use Ubiquity\utils\base\UArray;

/**
 * Manage events
 *
 * @author jc
 *
 */
class EventsManager {
	private static $key = "events/events";
	/**
	 *
	 * @var array|mixed
	 */
	protected static $managedEvents = [ ];

	public static function start() {
		if (CacheManager::$cache->exists ( self::$key )) {
			self::$managedEvents = CacheManager::$cache->fetch ( self::$key );
		}
	}

	public static function addListener($eventName, $action) {
		if (! isset ( self::$managedEvents [$eventName] )) {
			self::$managedEvents [$eventName] = [ ];
		}
		self::$managedEvents [$eventName] [] = $action;
	}

	public static function store() {
		CacheManager::$cache->store ( self::$key, "return " . UArray::asPhpArray ( self::$managedEvents, 'array' ) . ';' );
	}

	public static function trigger($eventName, &...$params) {
		if (isset ( self::$managedEvents [$eventName] )) {
			foreach ( self::$managedEvents [$eventName] as $action ) {
				self::triggerOne ( $action, $params );
			}
		}
	}

	private static function triggerOne($action, &$params) {
		if (\is_callable ( $action )) {
			\call_user_func_array ( $action, $params );
		} elseif (is_subclass_of ( $action, EventListenerInterface::class )) {
			\call_user_func_array ( [ new $action (),"on" ], $params );
		}
	}
}


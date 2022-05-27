<?php
namespace Ubiquity\events;

use Ubiquity\cache\CacheManager;

/**
 * Manage events
 *
 * @author jc
 *
 */
class EventsManager {

	private static string $key = 'events/events';

	/**
	 *
	 * @var array|mixed
	 */
	protected static $managedEvents = [];

	/**
	 * Starts the event manager (in app/config/services.php)
	 */
	public static function start():void {
		if (CacheManager::$cache->exists(self::$key)) {
			self::$managedEvents = CacheManager::$cache->fetch(self::$key);
		}
	}

	/**
	 * Adds a listener on eventName
	 * @param string $eventName
	 * @param EventListenerInterface|callable $action
	 */
	public static function addListener(string $eventName, $action):void {
		if (! isset(self::$managedEvents[$eventName])) {
			self::$managedEvents[$eventName] = [];
		}
		self::$managedEvents[$eventName][] = $action;
	}

	/**
	 * Store the managed events in cache (do not use in prod)
	 */
	public static function store():void {
		CacheManager::$cache->store(self::$key, self::$managedEvents);
	}

	/**
	 * Trigger an event
	 * @param string $eventName
	 * @param mixed ...$params
	 */
	public static function trigger(string $eventName, &...$params):void {
		if (isset(self::$managedEvents[$eventName])) {
			foreach (self::$managedEvents[$eventName] as $action) {
				self::triggerOne($action, $params);
			}
		}
	}

	private static function triggerOne($action, &$params):void {
		if (\is_callable($action)) {
			\call_user_func_array($action, $params);
		} elseif (is_subclass_of($action, EventListenerInterface::class)) {
			\call_user_func_array([
				new $action(),
				'on'
			], $params);
		}
	}
}


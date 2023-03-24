<?php
namespace Ubiquity\events;

use Ubiquity\cache\CacheManager;

/**
 * Manage events
 *
 * Ubiquity\contents\transformation\events$EventsManager
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.2
 *
 */
class EventsManager {

    private static string $key = 'events/events';

    /**
     *
     * @var array|mixed
     */
    protected static $managedEvents = [];

    public static function addOneListener(string $eventName, $action):void {
        self::$managedEvents[$eventName] ??= [];
        self::$managedEvents[$eventName][] = $action;
    }

    /**
     * Starts the event manager (in app/config/services.php)
     */
    public static function start():void {
        if (CacheManager::$cache->exists(self::$key)) {
            self::$managedEvents = CacheManager::$cache->fetch(self::$key);
        }
    }


    /**
     * Adds a listener on eventName.
     * @param string|array $eventNames
     * @param EventListenerInterface|callable $action
     */
    public static function addListener($eventNames, $action):void {
        if (\is_array($eventNames)) {
            foreach ($eventNames as $eventName) {
                self::addOneListener($eventName, $action);
            }
        } else {
            self::addOneListener($eventNames, $action);
        }
    }

    /**
     * Adds a list of listeners
     * @param array $listeners
     */
    public static function addListeners(array $listeners):void {
        foreach ($listeners as $eventName => $action) {
            self::addOneListener($eventName, $action);
        }
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


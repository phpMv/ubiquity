<?php

namespace Ubiquity\contents\validation\traits;

use Ubiquity\cache\CacheManager;
use Ubiquity\log\Logger;

/**
 * Ubiquity\contents\validation\traits$ValidatorsManagerCacheTrait
 * This class is part of Ubiquity
 * @author jc
 * @version 1.0.0
 * 
 * @property array $validatorTypes
 * @property array $instanceValidators
 *
 */
trait ValidatorsManagerCacheTrait {
	
	protected static $cache;

	protected static $key = 'contents/validators/';
	
	private static function getCacheValidators($instance, $group = '') {
		return self::getClassCacheValidators ( \get_class ( $instance ), $group );
	}
	
	protected static function getClassCacheValidators($class, $group = '') {
		if (isset ( self::$cache )) {
			$key = self::getHash ( $class . $group );
			if (self::$cache->exists ( $key )) {
				return self::$cache->fetch ( $key );
			}
		}
		return false;
	}
	
	protected static function getHash($class) {
		return \hash ( 'sha1', $class );
	}
	
	protected static function getModelCacheKey($classname) {
		return self::$key . \str_replace ( '\\', \DS, $classname );
	}
	
	protected static function store($model, $validators) {
		CacheManager::$cache->store ( self::getModelCacheKey ( $model ), $validators, 'validators' );
	}
	
	protected static function fetch($model) {
		$key = self::getModelCacheKey ( $model );
		if (CacheManager::$cache->exists ( $key )) {
			return CacheManager::$cache->fetch ( $key );
		}
		return [ ];
	}
	
	public static function clearCache($model = null, $group = '') {
		if (isset ( self::$cache )) {
			if (isset ( $model )) {
				$key = self::getHash ( $model . $group );
				self::$cache->remove ( $key );
			} else {
				self::$cache->clear ();
			}
		}
	}
	
	protected static function validateFromCache_($instance, $members, $excludedValidators = [ ]) {
		$result = [ ];
		$types = \array_flip ( self::$validatorTypes );
		foreach ( $members as $accessor => $validators ) {
			foreach ( $validators as $validatorInstance ) {
				$typeV = $types [get_class ( $validatorInstance )];
				if (! isset ( $excludedValidators [$typeV] )) {
					$valid = $validatorInstance->validate_ ( $instance->$accessor () );
					if ($valid !== true) {
						$result [] = $valid;
					}
				}
			}
		}
		return $result;
	}
	
	protected static function getUIConstraintsFromCache_($instance, $members, $excludedValidators = [ ]) {
		$result = [ ];
		$types = \array_flip ( self::$validatorTypes );
		foreach ( $members as $accessor => $validators ) {
			$member = \lcfirst ( \ltrim ( 'get', $accessor ) );
			foreach ( $validators as $validatorInstance ) {
				$typeV = $types [get_class ( $validatorInstance )];
				if (! isset ( $excludedValidators [$typeV] )) {
					$result [$member] += $validatorInstance->asUI ();
				}
			}
		}
		return $result;
	}
	
	/**
	 * Initializes the cache (SessionCache) for the class of instance
	 *
	 * @param object $instance
	 * @param string $group
	 */
	public static function initCacheInstanceValidators($instance, $group = '') {
		$class = \get_class ( $instance );
		$members = self::fetch ( $class );
		self::initInstancesValidators ( $instance, $members, $group );
	}
	
	protected static function initInstancesValidators($instance, $members, $group = '') {
		$class = \get_class ( $instance );
		$result = [ ];
		foreach ( $members as $member => $validators ) {
			$accessor = 'get' . \ucfirst ( $member );
			if (\method_exists ( $instance, $accessor )) {
				foreach ( $validators as $validator ) {
					$validatorInstance = self::getValidatorInstance ( $validator ['type'] );
					if ($validatorInstance !== false) {
						$validatorInstance->setValidationParameters ( $member, $validator ['constraints'] ?? [ ], $validator ['severity'] ?? null, $validator ['message'] ?? null);
						if ($group === '' || (isset ( $validator ['group'] ) && $validator ['group'] === $group)) {
							self::$instanceValidators [$class] [$accessor] [] = $validatorInstance;
							$result [$accessor] [] = $validatorInstance;
						}
					}
				}
			}
		}
		self::$cache->store ( self::getHash ( $class . $group ), $result );
	}
	
	protected static function getValidatorInstance($type) {
		if (isset ( self::$validatorTypes [$type] )) {
			$class = self::$validatorTypes [$type];
			return new $class ();
		} else {
			Logger::warn ( 'validation', "Validator $type does not exists!" );
			return false;
		}
	}
}


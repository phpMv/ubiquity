<?php

namespace Ubiquity\contents\normalizers;

use Ubiquity\exceptions\NormalizerException;
use Ubiquity\cache\CacheManager;
use Ubiquity\utils\base\UArray;

/**
 * Normalize objects and arrays of objects
 *
 * Ubiquity\contents\normalizers$NormalizersManager
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.1
 *
 */
class NormalizersManager {
	/**
	 *
	 * @var array|mixed
	 */
	private static $normalizers = [ ];
	private static $key = "contents/normalizers";

	public static function start() {
		if (CacheManager::$cache->exists ( self::$key )) {
			self::$normalizers = CacheManager::$cache->fetch ( self::$key );
		}
	}

	public static function registerClass($classname, $normalizer, $constructorParameters = [ ]) {
		$reflect = new \ReflectionClass ( $normalizer );
		self::$normalizers [$classname] = $reflect->newInstanceArgs ( $constructorParameters );
	}

	public static function registerClasses($classesAndNormalizers, ...$constructorParameters) {
		foreach ( $classesAndNormalizers as $class => $normalizer ) {
			self::registerClass ( $class, $normalizer, $constructorParameters );
		}
	}

	public static function getNormalizer($classname) {
		if (isset ( self::$normalizers [$classname] )) {
			return self::$normalizers [$classname];
		}
		throw new NormalizerException ( $classname . "has no serializer. Use NormalizersManager::registerClass to associate a new serializer." );
	}

	public static function normalizeArray(array $datas, NormalizerInterface $normalizer) {
		$result = [ ];
		foreach ( $datas as $object ) {
			if ($normalizer->supportsNormalization ( $object )) {
				$result [] = $normalizer->normalize ( $object );
			}
		}
		return $result;
	}

	public static function normalizeArray_(array $datas) {
		if (count ( $datas ) > 0) {
			$normalizer = self::getNormalizer ( get_class ( current ( $datas ) ) );
			if (isset ( $normalizer )) {
				return self::normalizeArray ( $datas, $normalizer );
			}
		}
		return [ ];
	}

	public static function normalize($object, NormalizerInterface $normalizer) {
		if ($normalizer->supportsNormalization ( $object )) {
			return $normalizer->normalize ( $object );
		}
		throw new NormalizerException ( get_class ( $object ) . " does not supports " . get_class ( $normalizer ) . " normalization." );
	}

	public static function normalize_($object) {
		$normalizer = self::getNormalizer ( get_class ( $object ) );
		if (isset ( $normalizer )) {
			return self::normalize ( $object, $normalizer );
		}
	}

	public static function store() {
		CacheManager::$cache->store ( self::$key, self::$normalizers );
	}
}


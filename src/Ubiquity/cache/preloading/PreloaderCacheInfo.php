<?php

namespace Ubiquity\cache\preloading;

/**
 * Gives some informations about opcache preloading.
 *
 * Ubiquity\cache\preloading$PreloaderCacheInfo
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
class PreloaderCacheInfo {
	private static const PRELOAD_KEY_CACHE = 'preload_statistics';

	private static function getStatus() {
		return \opcache_get_status ();
	}

	private static function getElement($part, $default): ?array {
		return self::getStatus () [self::PRELOAD_KEY_CACHE] [$part] ?? $default;
	}

	/**
	 * Returns true if opcache preloader is activated.
	 *
	 * @return bool
	 */
	public static function isActive(): bool {
		return \is_array ( self::getStatistics () );
	}

	/**
	 * Returrns the opcache preload statistics
	 *
	 * @return NULL|array
	 */
	public static function getStatistics(): ?array {
		return self::getStatus () [self::PRELOAD_KEY_CACHE] ?? null;
	}

	/**
	 * Returns the preloader memory consumption.
	 *
	 * @return array|NULL
	 */
	public static function getMemoryConsumption(): ?array {
		return self::getElement ( self::PRELOAD_KEY_CACHE, 0 );
	}

	/**
	 * Returns the list of preloaded functions.
	 *
	 * @return array|NULL
	 */
	public static function getFunctions(): ?array {
		return self::getElement ( 'functions', [ ] );
	}

	/**
	 * Returns the list of preloaded scripts
	 *
	 * @return array|NULL
	 */
	public static function getScripts(): ?array {
		return self::getElement ( 'scripts', [ ] );
	}

	/**
	 * Returns the list of preloaded classes
	 *
	 * @return array|NULL
	 */
	public static function getClasses(): ?array {
		return self::getElement ( 'classes', [ ] );
	}
}


<?php

namespace Ubiquity\translation;

use Ubiquity\translation\loader\ArrayLoader;
use Ubiquity\log\Logger;
use Ubiquity\utils\base\UFileSystem;
use Ubiquity\utils\http\URequest;

/**
 * Manage translations.
 * Use the start method to start the Manager, after starting the cache manager
 * Ubiquity\translation$TranslatorManager
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.3
 *
 */
class TranslatorManager {
	protected static $locale;
	protected static $loader;
	protected static $catalogues;
	protected static $fallbackLocale;

	/**
	 *
	 * @param callable $callback
	 * @param string $id
	 * @param array $parameters
	 * @param string $domain
	 * @param string $locale
	 * @return string
	 */
	protected static function transCallable($callback, $id, array $parameters = array(), $domain = null, $locale = null) {
		if (null === $domain) {
			$domain = 'messages';
		}
		$id = ( string ) $id;
		$catalogue = self::getCatalogue ( $locale );
		if ($catalogue === false) {
			if (isset ( self::$fallbackLocale ) && $locale !== self::$fallbackLocale) {
				self::setLocale ( self::$fallbackLocale );
				Logger::warn ( 'Translation', 'Locale ' . $locale . ' not found, set active locale to ' . self::$locale );
				return self::trans ( $id, $parameters, $domain, self::$locale );
			} else {
				Logger::error ( 'Translation', 'Locale not found, no valid fallbackLocale specified' );
				return $id;
			}
		}
		$transId = self::getTransId ( $id, $domain );
		if (isset ( $catalogue [$transId] )) {
			return $callback ( $catalogue [$transId], $parameters );
		} elseif (self::$fallbackLocale !== null && $locale !== self::$fallbackLocale) {
			Logger::warn ( 'Translation', 'Translation not found for ' . $id . '. Switch to fallbackLocale ' . self::$fallbackLocale );
			return self::trans ( $id, $parameters, $domain, self::$fallbackLocale );
		} else {
			Logger::warn ( 'Translation', 'Translation not found for ' . $id . '. in locales.' );
			return $id;
		}
	}

	protected static function replaceParams($trans, array $parameters = array()) {
		foreach ( $parameters as $k => $v ) {
			$trans = str_replace ( '%' . $k . '%', $v, $trans );
		}
		return $trans;
	}

	protected static function getTransId($id, $domain) {
		return $domain . '.' . $id;
	}

	protected static function assertValidLocale($locale) {
		if (1 !== preg_match ( '/^[a-z0-9@_\\.\\-]*$/i', $locale )) {
			throw new \InvalidArgumentException ( sprintf ( 'Invalid "%s" locale.', $locale ) );
		}
	}

	/**
	 * Starts the translator manager.
	 * This operation must be performed after the CacheManager has been started
	 *
	 * @param string $locale The active locale
	 * @param string $fallbackLocale The fallback locale
	 * @param string $rootDir The root dir for translation (default: appRoot/translations)
	 */
	public static function start($locale = 'en_EN', $fallbackLocale = null, $rootDir = null) {
		self::$locale = $locale;
		self::$fallbackLocale = $fallbackLocale;
		self::setRootDir ( $rootDir );
	}

	/**
	 * Defines the active locale
	 *
	 * @param string $locale
	 */
	public static function setLocale($locale) {
		self::assertValidLocale ( $locale );
		self::$locale = $locale;
	}

	/**
	 * Sets the complete directory for translations.
	 * Default: appRoot/translations
	 *
	 * @param string $rootDir
	 */
	public static function setRootDir($rootDir = null) {
		if (! isset ( $rootDir )) {
			$rootDir = \ROOT . \DS . 'translations';
		}
		self::$loader = new ArrayLoader ( $rootDir );
	}

	/**
	 * Returns the active locale
	 *
	 * @return string
	 */
	public static function getLocale() {
		return self::$locale;
	}

	/**
	 * Returns a translation corresponding to an id, using eventually some parameters
	 *
	 * @param string $id
	 * @param array $parameters
	 * @param string $domain
	 * @param string $locale
	 * @return string
	 */
	public static function trans($id, array $parameters = array(), $domain = null, $locale = null) {
		return self::transCallable ( function ($catalog, $parameters) {
			return self::replaceParams ( $catalog, $parameters );
		}, $id, $parameters, $domain, $locale );
	}

	/**
	 * Returns the translations catalog of the locale
	 *
	 * @param string $locale
	 * @return array
	 */
	public static function getCatalogue(&$locale = null) {
		if (null === $locale) {
			$locale = self::getLocale ();
		} else {
			self::assertValidLocale ( $locale );
		}
		if (! isset ( self::$catalogues [$locale] )) {
			self::loadCatalogue ( $locale );
		}
		return self::$catalogues [$locale];
	}

	/**
	 * Loads a catalog for a locale
	 *
	 * @param string $locale
	 */
	public static function loadCatalogue($locale = null) {
		self::$catalogues [$locale] = self::$loader->load ( $locale );
	}

	/**
	 * Returns the fallbackLocale
	 *
	 * @return string
	 */
	public static function getFallbackLocale() {
		return self::$fallbackLocale;
	}

	/**
	 * Sets the fallbackLocale
	 *
	 * @param string $fallbackLocale
	 */
	public static function setFallbackLocale($fallbackLocale) {
		self::$fallbackLocale = $fallbackLocale;
	}

	/**
	 * Clears the translations cache
	 */
	public static function clearCache() {
		self::$loader->clearCache ( '*' );
	}

	/**
	 * Returns the available locales
	 *
	 * @return string[]
	 */
	public static function getLocales() {
		$locales = [ ];
		$dirs = \glob ( self::getRootDir () . \DS . '*', GLOB_ONLYDIR );
		foreach ( $dirs as $dir ) {
			$locales [] = basename ( $dir );
		}
		return $locales;
	}

	/**
	 * Returns translations root dir
	 *
	 * @return string
	 */
	public static function getRootDir() {
		return self::$loader->getRootDir ();
	}

	/**
	 * Returns the active loader
	 *
	 * @return \Ubiquity\translation\loader\ArrayLoader
	 */
	public static function getLoader() {
		return self::$loader;
	}

	/**
	 * Returns all catalogs
	 *
	 * @return mixed
	 */
	public static function getCatalogues() {
		return self::$catalogues;
	}

	/**
	 * Creates default translations root directory
	 *
	 * @param string $rootDir
	 * @return string[]
	 */
	public static function initialize($rootDir = null) {
		self::setRootDir ( $rootDir );
		$locale = URequest::getDefaultLanguage ();
		UFileSystem::safeMkdir ( self::getRootDir () . \DS . $locale );
		return self::getLocales ();
	}
}

<?php

namespace Ubiquity\translation;

use Ubiquity\exceptions\CacheException;
use Ubiquity\log\Logger;
use Ubiquity\translation\loader\ArrayLoader;
use Ubiquity\utils\base\UFileSystem;
use Ubiquity\utils\http\URequest;

/**
 * Manage translations.
 * Use the start method to start the Manager, after starting the cache manager
 * Ubiquity\translation$TranslatorManager
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.5
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

	/**
	 * Inspired by \Symfony\Contracts\Translation\TranslatorTrait$trans
	 *
	 * @param string $message
	 * @param array $choice
	 * @param array $parameters
	 * @return string
	 */
	protected static function doChoice($message, array $choice, array $parameters = []) {
		$message = ( string ) $message;

		$number = ( float ) current ( $choice );
		$parameters = $parameters + $choice;
		$parts = [ ];
		if (preg_match ( '/^\|++$/', $message )) {
			$parts = explode ( '|', $message );
		} elseif (preg_match_all ( '/(?:\|\||[^\|])++/', $message, $matches )) {
			$parts = $matches [0];
		}
		$intervalRegexp = <<<'EOF'
/^(?P<interval>
    ({\s*
        (\-?\d+(\.\d+)?[\s*,\s*\-?\d+(\.\d+)?]*)
    \s*})
        |
    (?P<left_delimiter>[\[\]])
        \s*
        (?P<left>-Inf|\-?\d+(\.\d+)?)
        \s*,\s*
        (?P<right>\+?Inf|\-?\d+(\.\d+)?)
        \s*
    (?P<right_delimiter>[\[\]])
)\s*(?P<message>.*?)$/xs
EOF;
		foreach ( $parts as $part ) {
			$part = trim ( str_replace ( '||', '|', $part ) );
			if (preg_match ( $intervalRegexp, $part, $matches )) {
				if ($matches [2]) {
					foreach ( explode ( ',', $matches [3] ) as $n ) {
						if ($number == $n) {
							return self::replaceParams ( $matches ['message'], $parameters );
						}
					}
				} else {
					$leftNumber = '-Inf' === $matches ['left'] ? - INF : ( float ) $matches ['left'];
					$rightNumber = \is_numeric ( $matches ['right'] ) ? ( float ) $matches ['right'] : INF;
					if (('[' === $matches ['left_delimiter'] ? $number >= $leftNumber : $number > $leftNumber) && (']' === $matches ['right_delimiter'] ? $number <= $rightNumber : $number < $rightNumber)) {
						return self::replaceParams ( $matches ['message'], $parameters );
					}
				}
			} else {
				return self::replaceParams ( $message, $parameters );
			}
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

	public static function isValidLocale($locale) {
		return (1 === preg_match ( '/^[a-z0-9@_\\.\\-]*$/i', $locale ));
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
		return self::transCallable ( function ($trans, $parameters) {
			return self::replaceParams ( $trans, $parameters );
		}, $id, $parameters, $domain, $locale );
	}

	/**
	 * Returns a translation with choices corresponding to an id, using eventually some parameters
	 *
	 * @param string $id
	 * @param array $choice
	 * @param array $parameters
	 * @param string $domain
	 * @param string $locale
	 * @return string
	 */
	public static function transChoice($id, array $choice, array $parameters = array(), $domain = null, $locale = null) {
		return self::transCallable ( function ($message, $parameters) use ($choice) {
			return self::doChoice ( $message, $choice, $parameters );
		}, $id, $parameters, $domain, $locale );
	}

	/**
	 * Returns the translations catalog of the locale
	 *
	 * @param string $locale
	 * @return array|boolean
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
		return self::$catalogues [$locale] ?? false;
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
	 * Clears the $locale translations cache
	 */
	public static function clearLocaleCache($locale) {
		self::$loader->clearCache ( $locale );
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
	 * Returns the existing domains in $locale
	 *
	 * @param string $locale
	 * @return array
	 */
	public static function getDomains($locale) {
		$catalog = new MessagesCatalog ( $locale, self::$loader );
		return $catalog->getDomains ();
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
		$locale = URequest::getDefaultLanguage ();
		self::createLocale ( self::fixLocale ( $locale ), $rootDir );
		return self::getLocales ();
	}

	public static function fixLocale($language) {
		return str_replace ( [ '-','.' ], '_', $language );
	}

	/**
	 * Creates the locale folder in translations root directory
	 *
	 * @param string $locale
	 * @param string $rootDir
	 */
	public static function createLocale($locale, $rootDir = null) {
		self::setRootDir ( $rootDir );
		UFileSystem::safeMkdir ( self::getRootDir () . \DS . $locale );
	}

	/**
	 * Creates a new domain in $locale.
	 * throws a CacheException if TranslatorManager is not started
	 *
	 * @param string $locale
	 * @param string $domain
	 * @param array|null $defaultValues
	 * @throws CacheException
	 * @return \Ubiquity\translation\MessagesDomain|boolean
	 */
	public static function createDomain($locale, $domain, $defaultValues = null) {
		if (isset ( self::$loader )) {
			$domains = self::getDomains ( $locale );
			if (array_search ( $domain, $domains ) === false) {
				$dom = new MessagesDomain ( $locale, self::$loader, $domain );
				if (is_array ( $defaultValues )) {
					$dom->setMessages ( $defaultValues );
				}
				$dom->store ();
				return $dom;
			}
			return false;
		}
		throw new CacheException ( 'TranslatorManager is not started!' );
	}

	/**
	 * Check if the cache exists for a $domain in $locale
	 *
	 * @param string $locale
	 * @param string $domain
	 * @return boolean
	 */
	public static function cacheExist($locale, $domain = '*') {
		return self::$loader->cacheExists ( $locale, $domain );
	}
}

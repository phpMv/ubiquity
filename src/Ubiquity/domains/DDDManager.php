<?php


namespace Ubiquity\domains;

use Ubiquity\controllers\Startup;

/**
 * Manager for a Domain Driven Design approach.
 * Ubiquity\domains$DDDManager
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 0.0.0
 *
 */
class DDDManager {
	private static $base='domains';

	public static function start(string $base='domains'): void{
		self::$base=$base;
	}

	public static function setDomain(string $domain): void {
		Startup::setActiveDomain($domain,self::$base);
	}

	public static function resetActiveDomain(): void {
		Startup::resetActiveDomain();
	}

	public static function getDomains(): array {
		return \glob(\ROOT.self::$base . '/*' , \GLOB_ONLYDIR);
	}

	public static function hasDomains(): bool {
		return \file_exists(\ROOT.self::$base) && \count(self::getDomains())>0;
	}

}
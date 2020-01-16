<?php

namespace Ubiquity\controllers;

use Ubiquity\log\Logger;

/**
 * Startup for async platforms (Swoole, Workerman, php-pm...)
 * Ubiquity\controllers$StartupAsync
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
class StartupAsync extends Startup {
	private const IS_VALID = 1;
	private const INITIALIZE = 2;
	private const FINALIZE = 4;
	private static $controllers = [ ];

	public static function runAction(array &$u, $initialize = true, $finalize = true): void {
		self::$controller = $ctrl = $u [0];
		self::$action = $action = $u [1] ?? 'index';
		self::$actionParams = \array_slice ( $u, 2 );

		try {
			if (null !== $controller = self::getControllerInstance ( $ctrl )) {
				$binaryCalls = $controller->_binaryCalls ?? (self::IS_VALID + self::INITIALIZE + self::FINALIZE);
				if (($binaryCalls & self::IS_VALID) && ! $controller->isValid ( $action )) {
					$controller->onInvalidControl ();
				} else {
					if (($binaryCalls & self::INITIALIZE) && $initialize) {
						$controller->initialize ();
					}
					try {
						$controller->$action ( ...(self::$actionParams) );
					} catch ( \Error $e ) {
						Logger::warn ( 'Startup', $e->getTraceAsString (), 'runAction' );
						if (self::$config ['debug']) {
							throw $e;
						}
					}
					if (($binaryCalls & self::FINALIZE) && $finalize) {
						$controller->finalize ();
					}
				}
			}
		} catch ( \Error $eC ) {
			Logger::warn ( 'Startup', $eC->getTraceAsString (), 'runAction' );
			if (self::$config ['debug']) {
				throw $eC;
			}
		}
	}

	public static function getControllerInstance($controllerName): ?object {
		if (! isset ( self::$controllers [$controllerName] )) {
			return self::$controllers [$controllerName] = self::_getControllerInstance ( $controllerName );
		}
		return self::$controllers [$controllerName];
	}
}


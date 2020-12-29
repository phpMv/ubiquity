<?php

namespace Ubiquity\cache\traits;

use Ubiquity\cache\ClassUtils;
use Ubiquity\cache\parser\ControllerParser;
use Ubiquity\controllers\Startup;
use Ubiquity\controllers\di\DiManager;
use Ubiquity\utils\base\UArray;

/**
 * Ubiquity\cache\traits$DevRouterCacheTrait
 * This class is part of Ubiquity
 * @author jc
 * @version 1.0.11
 *
 */
trait DevRouterCacheTrait {
	abstract public static function getAnnotationsEngineInstance();

	private static function addControllerCache($classname) {
		$parser = new ControllerParser (self::getAnnotationsEngineInstance());
		try {
			$parser->parse ( $classname );
			return $parser->asArray ();
		} catch ( \Exception $e ) {
			// Nothing to do
		}
		return [ ];
	}

	private static function parseControllerFiles(&$config, $silent = false) {
		$routes = [ 'rest' => [ ],'default' => [ ] ];
		$files = self::getControllersFiles ( $config, $silent );
		$annotsEngine=self::getAnnotationsEngineInstance();
		foreach ( $files as $file ) {
			if (is_file ( $file )) {
				$controller = ClassUtils::getClassFullNameFromFile ( $file );
				$parser = new ControllerParser ($annotsEngine);
				try {
					$parser->parse ( $controller );
					$ret = $parser->asArray ();
					$key = ($parser->isRest ()) ? 'rest' : 'default';
					$routes [$key] = \array_merge ( $routes [$key], $ret );
				} catch ( \Exception $e ) {
					// Nothing to do
				}
			}
		}
		self::sortByPriority ( $routes ['default'] );
		self::sortByPriority ( $routes ['rest'] );
		return $routes;
	}

	protected static function sortByPriority(&$array) {
		\uasort ( $array, function ($item1, $item2) {
			return UArray::getRecursive ( $item2, 'priority', 0 ) <=> UArray::getRecursive ( $item1, 'priority', 0 );
		} );
			UArray::removeRecursive ( $array, 'priority' );
	}

	protected static function initRouterCache(&$config, $silent = false) {
		$routes = self::parseControllerFiles ( $config, $silent );
		self::$cache->store ( 'controllers/routes.default', $routes ['default'], 'controllers' );
		self::$cache->store ( 'controllers/routes.rest', $routes ['rest'], 'controllers' );
		DiManager::init ( $config );
		if (! $silent) {
			echo "Router cache reset\n";
		}
	}

	public static function getControllersFiles(&$config, $silent = false) {
		return self::_getFiles ( $config, 'controllers', $silent );
	}

	public static function getControllers($subClass = "\\Ubiquity\\controllers\\Controller", $backslash = false, $includeSubclass = false, $includeAbstract = false) {
		$result = [ ];
		if ($includeSubclass) {
			$result [] = $subClass;
		}
		$config = Startup::getConfig ();
		$files = self::getControllersFiles ( $config, true );
		try {
			$restCtrls = self::getRestCache ();
		} catch ( \Exception $e ) {
			$restCtrls = [ ];
		}
		foreach ( $files as $file ) {
			if (\is_file ( $file )) {
				$controllerClass = ClassUtils::getClassFullNameFromFile ( $file, $backslash );
				if (\class_exists ( $controllerClass ) && isset ( $restCtrls [$controllerClass] ) === false) {
					$r = new \ReflectionClass ( $controllerClass );
					if ($r->isSubclassOf ( $subClass ) && ($includeAbstract || ! $r->isAbstract ())) {
						$result [] = $controllerClass;
					}
				}
			}
		}
		return $result;
	}
}


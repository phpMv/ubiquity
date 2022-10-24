<?php

namespace Ubiquity\cache\traits;

use Ubiquity\cache\ClassUtils;
use Ubiquity\cache\parser\ControllerParser;
use Ubiquity\controllers\Startup;
use Ubiquity\controllers\di\DiManager;
use Ubiquity\utils\base\UArray;
use Ubiquity\exceptions\ParserException;

/**
 * Ubiquity\cache\traits$DevRouterCacheTrait
 * This class is part of Ubiquity
 *
 * @author jc
 * @version 1.0.15
 *
 */
trait DevRouterCacheTrait {
	
	abstract public static function getAnnotationsEngineInstance();
	
	private static function addControllerCache(string $classname): array {
		$parser = new ControllerParser ( self::getAnnotationsEngineInstance () );
		try {
			$parser->parse ( $classname );
			return $parser->asArray ();
		} catch ( \Exception $e ) {
			// Nothing to do
		}
		return [ ];
	}
	
	private static function parseControllerFiles(array &$config, bool $silent = false,bool $activeDomain=false): array {
		$routes = [ 'rest' => [ ],'default' => [ ] ];
		if($activeDomain){
			$files=self::getControllersFiles($config,$silent);
		}else {
			$files = self::getAllControllersFiles($config, $silent);
		}
		$annotsEngine = self::getAnnotationsEngineInstance ();
		foreach ( $files as $file ) {
			if (is_file ( $file )) {
				$controller = ClassUtils::getClassFullNameFromFile ( $file );
				$parser = new ControllerParser ( $annotsEngine );
				$parser->setSilent($silent);
				try {
					$parser->parse ( $controller);
					$ret = $parser->asArray ();
					$key = ($parser->isRest ()) ? 'rest' : 'default';
					$routes [$key] = \array_merge ( $routes [$key], $ret );
				} catch ( \Exception $e ) {
					if (!$silent && $e instanceof ParserException) {
						throw $e;
					}
					// Nothing to do
				}
			}
		}
		self::sortByPriority ( $routes ['default'] );
		self::sortByPriority ( $routes ['rest'] );
		$routes ['rest-index'] = self::createIndex ( $routes ['rest'] );
		$routes ['default-index'] = self::createIndex ( $routes ['default'] );
		return $routes;
	}
	
	private static function hasCapturingGroup(string $expression): bool {
		return \preg_match ( "~\\\\.(*SKIP)(?!)|\((?(?=\?)\?(P?['<]\w+['>]))~", $expression )===1;
	}
	
	public static function getFirstPartIndex(string $element): string {
		return \strtok ( \trim ( $element, '/' ), '/' );
	}
	
	protected static function createIndex(array $routes): array {
		$res = [ ];
		foreach ( $routes as $path => $route ) {
			if (self::hasCapturingGroup ( $path )) {
				$part = self::getFirstPartIndex ( $path );
				if ($part != null) {
					if($part===\trim($path,'/')){
						$part='*';
					}
					$res [$part] [] = $path;
				}
			}
		}
		return $res;
	}
	
	protected static function sortByPriority(array &$array): void {
		\uasort ( $array, function ($item1, $item2) {
			return UArray::getRecursive ( $item2, 'priority', 0 ) <=> UArray::getRecursive ( $item1, 'priority', 0 );
		} );
			UArray::removeRecursive ( $array, 'priority' );
	}
	
	protected static function initRouterCache(array &$config, bool $silent = false): void {
		$routes = self::parseControllerFiles ( $config, $silent );
		self::$cache->store ( 'controllers/routes.default', $routes ['default'], 'controllers' );
		self::$cache->store ( 'controllers/routes.rest', $routes ['rest'], 'controllers' );
		self::$cache->store ( 'controllers/routes.default-index', $routes ['default-index'], 'controllers' );
		self::$cache->store ( 'controllers/routes.rest-index', $routes ['rest-index'], 'controllers' );
		DiManager::init ( $config );
		if (! $silent) {
			echo "Router cache reset\n";
		}
	}
	
	public static function getControllersFiles(array &$config, bool $silent = false): array {
		return self::_getFiles ( $config, 'controllers', $silent );
	}

	public static function getAllControllersFiles(array &$config, bool $silent = false): array {
		return self::_getAllFiles ( $config, 'controllers', $silent );
	}
	
	public static function getControllers(string $subClass = "\\Ubiquity\\controllers\\Controller", bool $backslash = false, bool $includeSubclass = false, bool $includeAbstract = false): array {
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

	
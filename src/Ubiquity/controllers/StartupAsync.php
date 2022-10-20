<?php

namespace Ubiquity\controllers;

use Ubiquity\log\Logger;

/**
 * Startup for async platforms (Swoole, Workerman, Roadrunner, php-pm...)
 * Ubiquity\controllers$StartupAsync
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.1.1
 *
 */
class StartupAsync extends Startup {
	private const IS_VALID = 1;
	private const INITIALIZE = 2;
	private const FINALIZE = 4;
	private static $controllers = [ ];
	
	private static $routes=[];
	
	/**
	 * Forwards to url
	 *
	 * @param string $url The url to forward to
	 * @param boolean $initialize If true, the **initialize** method of the controller is called
	 * @param boolean $finalize If true, the **finalize** method of the controller is called
	 */
	public static function forward(string $url, bool $initialize = true, bool $finalize = true): void {
		$methodUrl=$_SERVER['REQUEST_METHOD'].$url;
		if(isset(self::$routes[$methodUrl])){
			$m=self::$routes[$methodUrl];
			$m($initialize,$finalize);
			return ;
		}
		$u = self::parseUrl ( $url );
		if (\is_array ( Router::getRoutes () ) && ($ru = Router::getRoute ( $url, true, self::$config ['debug'] ?? false)) !== false) {
			if (\is_array ( $ru )) {
				if (isset ( $ru ['controller'] )) {
					(self::$routes[$methodUrl]=function($i,$f)use($ru){
						static::runAction ( $ru, $i, $f );
					})($initialize,$finalize);
				} else {
					(self::$routes[$methodUrl]=function() use($ru){
						self::runCallable ( $ru );
					})();
				}
			} else {
				(self::$routes[$methodUrl]=function() use($ru){
					echo $ru; // Displays route response from cache
				})();
				
			}
		} else {
			$ru =['controller'=>self::$ctrlNS . $u [0],'action'=> $u [1]??'index','params'=> \array_slice ( $u, 2 )];
			(self::$routes[$methodUrl]=function($i,$f)use($ru){
				static::runAction ( $ru, $i, $f );
			})($initialize,$finalize);
		}
	}
	
	public static function runAction(array &$u, bool $initialize = true, bool $finalize = true): void {
		self::$controller = $ctrl = $u ['controller'];
		self::$action = $action = $u ['action'];
		self::$actionParams = $u['params']??[];
		
		try {
			if (null !== $controller = self::getControllerInstance ( $ctrl )) {
				if($mainParams=$u['mainParams']??false){
					static::setMainParams($controller,$mainParams);
				}
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
						if (! \method_exists ( $controller, $action )) {
							static::onError ( 404, "This action does not exist on the controller " . $ctrl, $controller );
						} else {
							static::logError($e->getCode(), $e->getMessage());
							if (self::$config ['debug']) {
								throw $e;
							} else {
								static::onError ( 500, $e->getMessage (), $controller );
							}
						}
					}
					if (($binaryCalls & self::FINALIZE) && $finalize) {
						$controller->finalize ();
					}
				}
			} else {
				Logger::warn ( 'Startup', 'The controller `' . $ctrl . '` doesn\'t exist! <br/>', 'runAction' );
				static::onError ( 404 );
			}
		} catch ( \Error $eC ) {
			static::logError($eC->getCode(), $eC->getMessage());
			if (self::$config ['debug']) {
				throw $eC;
			} else {
				static::onError ( 500, $eC->getMessage () );
			}
		}
	}
	
	public static function getControllerInstance(string $controllerName): ?object {
		return self::$controllers [$controllerName] ??= self::_getControllerInstance ( $controllerName );
	}
	
	public static function warmupAction(string $controller, string $action = 'index') {
		\ob_start ();
		$ru = [ 'controller'=>$controller,'action'=>$action ];
		static::runAction ( $ru, true, true );
		\ob_end_clean ();
	}
	
	public static function warmupForward(string $url, bool $initialize = true, bool $finalize = true): void {
		\ob_start ();
		static::forward( $url, $initialize, $finalize );
		\ob_end_clean ();
	}
}

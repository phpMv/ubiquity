<?php

namespace Ubiquity\controllers\admin\popo;

use Ubiquity\cache\CacheManager;
use Ubiquity\cache\ClassUtils;
use Ubiquity\controllers\Controller;
use Ubiquity\controllers\Router;
use Ubiquity\controllers\Startup;
use Ubiquity\controllers\seo\SeoController;
use Ubiquity\utils\base\UString;

class ControllerAction {
	private $controller;
	private $action;
	private $parameters;
	private $dValues;
	private $annots;
	private static $excludeds = [ "__construct","isValid","initialize","finalize","onInvalidControl","loadView","forward","redirectToRoute","getView","message","loadDefaultView","getDefaultViewName","showSimpleMessage","showConfMessage" ];
	public static $controllers = [ ];

	public function __construct($controller = "", $action = "", $parameters = [], $dValues = [], $annots = []) {
		$this->controller = $controller;
		$this->action = $action;
		$this->parameters = $parameters;
		$this->dValues = $dValues;
		$this->annots = $annots;
	}

	public static function initWithPath($url) {
		$result = [ ];
		$config = Startup::getConfig ();
		$ns = $config ["mvcNS"] ["controllers"];
		if ($ns !== "" && $ns !== null) {
			$ns .= "\\";
		}
		if (! $url) {
			$url = "_default";
		}
		if (UString::endswith ( $url, "/" ))
			$url = \substr ( $url, 0, strlen ( $url ) - 1 );
		$u = \explode ( "/", $url );
		$u [0] = $ns . $u [0];
		if (\class_exists ( $u [0] )) {
			$controllerClass = $u [0];
			if (\count ( $u ) < 2)
				$u [] = "index";
			if (\method_exists ( $controllerClass, $u [1] )) {
				$method = new \ReflectionMethod ( $u [0], $u [1] );
				$r = self::scanMethod ( $controllerClass, $method );
				if (isset ( $r ))
					$result [] = $r;
			}
		}
		return $result;
	}

	public static function init() {
		$result = [ ];
		$config = Startup::getConfig ();

		$files = CacheManager::getControllersFiles ( $config, true );
		try {
			$restCtrls = CacheManager::getRestCache ();
		} catch ( \Exception $e ) {
			$restCtrls = [ ];
		}

		foreach ( $files as $file ) {
			if (is_file ( $file )) {
				$controllerClass = ClassUtils::getClassFullNameFromFile ( $file );
				self::initFromClassname ( $result, $controllerClass, $restCtrls );
			}
		}
		return $result;
	}

	public static function initFromClassname(&$result, $controllerClass, $restCtrls = []) {
		if (class_exists ( $controllerClass, true ) && isset ( $restCtrls [$controllerClass] ) === false) {
			self::$controllers [] = $controllerClass;
			$reflect = new \ReflectionClass ( $controllerClass );
			if (! $reflect->isAbstract () && $reflect->isSubclassOf ( Controller::class ) && ! $reflect->isSubclassOf ( SeoController::class )) {
				$methods = $reflect->getMethods ( \ReflectionMethod::IS_PUBLIC );
				foreach ( $methods as $method ) {
					$r = self::scanMethod ( $controllerClass, $method );
					if (isset ( $r ))
						$result [] = $r;
				}
			}
		}
	}

	private static function scanMethod($controllerClass, \ReflectionMethod $method) {
		$result = null;
		if (\array_search ( $method->name, self::$excludeds ) === false && ! UString::startswith ( $method->name, "_" )) {
			$annots = Router::getAnnotations ( $controllerClass, $method->name );
			$parameters = $method->getParameters ();
			$defaults = [ ];
			foreach ( $parameters as $param ) {
				if ($param->isOptional () && ! $param->isVariadic ()) {
					$defaults [$param->name] = $param->getDefaultValue ();
				}
			}
			$result = new ControllerAction ( $controllerClass, $method->name, $parameters, $defaults, $annots );
		}
		return $result;
	}

	public function getController() {
		return $this->controller;
	}

	public function setController($controller) {
		$this->controller = $controller;
		return $this;
	}

	public function getAction() {
		return $this->action;
	}

	public function setAction($action) {
		$this->action = $action;
		return $this;
	}

	public function getParameters() {
		return $this->parameters;
	}

	public function setParameters($parameters) {
		$this->parameters = $parameters;
		return $this;
	}

	public function getDValues() {
		return $this->dValues;
	}

	public function setDValues($dValues) {
		$this->dValues = $dValues;
		return $this;
	}

	public function getAnnots() {
		return $this->annots;
	}

	public function setAnnots($annots) {
		$this->annots = $annots;
		return $this;
	}

	public function getPath() {
		$reflect = new \ReflectionClass ( $this->controller );
		return $reflect->getShortName () . "/" . $this->action;
	}

	public function getId() {
		return $this->getPath ();
	}
}

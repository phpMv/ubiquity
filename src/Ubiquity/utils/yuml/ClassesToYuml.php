<?php

namespace Ubiquity\utils\yuml;

use Ubiquity\cache\CacheManager;
use Ubiquity\controllers\Startup;
use Ubiquity\utils\base\UString;

/**
 * Generates Yuml code from models.
 * Ubiquity\utils\yuml$ClassesToYuml
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
class ClassesToYuml {
	private $displayProperties;
	private $displayAssociations;
	private $displayMethods;
	private $displayMethodsParams;
	private $displayPropertiesTypes;
	private $manyToManys;
	private $database;

	public function __construct($databaseOffset = 'default', $displayProperties = true, $displayAssociations = true, $displayMethods = false, $displayMethodsParams = false, $displayPropertiesTypes = false) {
		$this->database = $databaseOffset;
		$this->displayProperties = $displayProperties;
		$this->displayAssociations = $displayAssociations;
		$this->displayMethods = $displayMethods;
		$this->displayMethodsParams = $displayMethodsParams;
		$this->displayPropertiesTypes = $displayPropertiesTypes;
		$this->manyToManys = [ ];
	}

	/**
	 *
	 * @return ClassToYuml[]|string[]
	 */
	public function parse() {
		$yumlResult = [ ];
		$config = Startup::getConfig ();
		CacheManager::start ( $config );
		$models = CacheManager::getModels ( $config, true, $this->database );
		if (\sizeof ( $models ) !== 0) {
			foreach ( $models as $model ) {
				$yumlR = new ClassToYuml ( $model, $this->displayProperties, false, $this->displayMethods, $this->displayMethodsParams, $this->displayPropertiesTypes, false );
				$yumlR->loadManyToManys ();
				$yumlResult [] = $yumlR;
			}
			if ($this->displayAssociations) {
				$count = \sizeof ( $models );
				for($i = 0; $i < $count; $i ++) {
					$this->checkManyToManys ( $yumlResult [$i]->getExtManyToManys (), $yumlResult [$i] );
				}
				for($i = 0; $i < $count; $i ++) {
					$result = $yumlResult [$i]->oneToManyTostring ();
					if (UString::isNotNull ( $result )) {
						$yumlResult [] = $result;
					}
					$result = $yumlResult [$i]->manyToManyTostring ( false );
					if (UString::isNotNull ( $result )) {
						$yumlResult [] = $result;
					}
				}
			}
		}
		$this->manyToManys = [ ];
		return $yumlResult;
	}

	private function checkManyToManys($manyToManys, ClassToYuml $classToYuml) {
		foreach ( $manyToManys as $targetClass => $class ) {
			if (\array_search ( \md5 ( $class . '_' . $targetClass ), $this->manyToManys ) !== false || \array_search ( \md5 ( $targetClass . '_' . $class ), $this->manyToManys ) !== false) {
				$classToYuml->removeManyToManyExt ( $targetClass );
			} else {
				$this->manyToManys [] = \md5 ( $class . '_' . $targetClass );
			}
		}
	}

	public function __toString() {
		return \implode ( Yuml::$groupeSeparator, $this->parse () );
	}
}

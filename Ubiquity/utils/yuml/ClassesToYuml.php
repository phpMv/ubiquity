<?php

namespace Ubiquity\utils\yuml;

use Ubiquity\cache\CacheManager;
use Ubiquity\controllers\Startup;
use Ubiquity\cache\ClassUtils;
use Ubiquity\utils\base\UString;

class ClassesToYuml {
	private $displayProperties;
	private $displayAssociations;
	private $displayMethods;
	private $displayMethodsParams;
	private $displayPropertiesTypes;

	public function __construct($displayProperties = true, $displayAssociations = true, $displayMethods = false, $displayMethodsParams = false, $displayPropertiesTypes = false) {
		$this->displayProperties = $displayProperties;
		$this->displayAssociations = $displayAssociations;
		$this->displayMethods = $displayMethods;
		$this->displayMethodsParams = $displayMethodsParams;
		$this->displayPropertiesTypes = $displayPropertiesTypes;
	}

	/**
	 *
	 * @return ClassToYuml[]|string[]
	 */
	public function parse() {
		$yumlResult = [ ];
		$config = Startup::getConfig ();
		$files = CacheManager::getModelsFiles ( $config, true );
		if (\sizeof ( $files ) !== 0) {
			foreach ( $files as $file ) {
				$completeName = ClassUtils::getClassFullNameFromFile ( $file );
				$yumlR = new ClassToYuml ( $completeName, $this->displayProperties, false, $this->displayMethods, $this->displayMethodsParams, $this->displayPropertiesTypes, false );
				$yumlResult [] = $yumlR;
			}
			if ($this->displayAssociations) {
				$count = \sizeof ( $files );
				for($i = 0; $i < $count; $i ++) {
					$result = $yumlResult [$i]->oneToManyTostring ();
					if (UString::isNotNull ( $result ))
						$yumlResult [] = $result;
				}
			}
		}
		return $yumlResult;
	}

	public function __toString() {
		return \implode ( Yuml::$groupeSeparator, $this->parse () );
	}
}

<?php

namespace Ubiquity\utils;

use Ubiquity\cache\ClassUtils;

class UbiquityUtils {

	public static function getModelsName($config, $name) {
		$modelsNS = $config ['mvcNS'] ['models'];
		return ClassUtils::getClassNameWithNS ( $modelsNS, $name );
	}

	public static function getControllerName($config, $name) {
		$modelsNS = $config ['mvcNS'] ['controllers'];
		return ClassUtils::getClassNameWithNS ( $modelsNS, $name );
	}
}

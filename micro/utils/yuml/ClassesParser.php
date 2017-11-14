<?php

namespace micro\utils\yuml;

use micro\cache\CacheManager;
use micro\controllers\Startup;
use micro\cache\ClassUtils;
use micro\utils\StrUtils;

class ClassesParser {
	protected $displayProperties;
	protected $displayMethods;
	protected $displayAssociations;

	public function parse() {
		$yumlResult=[ ];
		$config=Startup::getConfig();
		$files=CacheManager::getModelsFiles($config, true);
		if (\sizeof($files) !== 0) {
			foreach ( $files as $file ) {
				$completeName=ClassUtils::getClassFullNameFromFile($file);
				$yumlR=new ClassParser($completeName, true, false, false, false, false, false);
				$yumlResult[]=$yumlR;
			}
			$count=\sizeof($files);
			for($i=0; $i < $count; $i++) {
				$result=$yumlResult[$i]->oneToManyTostring();
				if (StrUtils::isNotNull($result))
					$yumlResult[]=$result;
			}
		}
		return $yumlResult;
	}

	public function __toString() {
		return \implode(Yuml::$groupeSeparator, $this->parse());
	}
}

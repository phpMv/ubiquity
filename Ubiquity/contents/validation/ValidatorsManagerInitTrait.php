<?php

namespace Ubiquity\contents\validation;

use Ubiquity\cache\CacheManager;

/**
 * @author jcheron <myaddressmail@gmail.com>
 * @property array $validatorTypes
 */
trait ValidatorsManagerInitTrait {
	/**
	 * Parses models and save validators in cache
	 * to use in dev only
	 * @param array $config
	 */
	public static function initModelsValidators(&$config){
		$models=CacheManager::getModels($config,true);
		foreach ($models as $model){
			$parser=new ValidationModelParser();
			$parser->parse($model);
			$validators=$parser->getValidators();
			if(sizeof($validators)>0){
				self::store($model, $parser->__toString());
			}
		}
	}
	
	/**
	 * Registers a validator type for using with @validator annotation
	 * @param string $type
	 * @param string $validatorClass
	 */
	public static function registerType($type,$validatorClass){
		self::$validatorTypes[$type]=$validatorClass;
	}
}


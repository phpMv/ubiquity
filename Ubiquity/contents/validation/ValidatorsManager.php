<?php

namespace Ubiquity\contents\validation;

use Ubiquity\cache\CacheManager;
use Ubiquity\log\Logger;
use Ubiquity\contents\validation\validators\multiples\LengthValidator;
use Ubiquity\contents\validation\validators\multiples\IdValidator;
use Ubiquity\contents\validation\validators\basic\NotNullValidator;
use Ubiquity\contents\validation\validators\basic\NotEmptyValidator;
use Ubiquity\contents\validation\validators\comparison\EqualsValidator;
use Ubiquity\contents\validation\validators\basic\TypeValidator;
use Ubiquity\contents\validation\validators\comparison\GreaterThanValidator;
use Ubiquity\contents\validation\validators\comparison\LessThanValidator;
use Ubiquity\contents\validation\validators\basic\IsNullValidator;
use Ubiquity\contents\validation\validators\basic\IsEmptyValidator;
use Ubiquity\contents\validation\validators\basic\IsTrueValidator;
use Ubiquity\contents\validation\validators\basic\IsFalseValidator;
use Ubiquity\contents\validation\validators\strings\RegexValidator;
use Ubiquity\contents\validation\validators\strings\EmailValidator;
use Ubiquity\contents\validation\validators\strings\UrlValidator;

/**
 * Validators manager
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 */
class ValidatorsManager {
	
	protected static $validatorInstances=[];

	public static $validatorTypes=[
			"notNull"=>NotNullValidator::class,
			"isNull"=>IsNullValidator::class,
			"notEmpty"=>NotEmptyValidator::class,
			"isEmpty"=>IsEmptyValidator::class,
			"isTrue"=>IsTrueValidator::class,
			"isFalse"=>IsFalseValidator::class,
			"equals"=>EqualsValidator::class,
			"type"=>TypeValidator::class,
			"greaterThan"=>GreaterThanValidator::class,
			"lessThan"=>LessThanValidator::class,
			"length"=>LengthValidator::class,
			"id"=>IdValidator::class,
			"regex"=>RegexValidator::class,
			"email"=>EmailValidator::class,
			"url"=>UrlValidator::class
			
	];
	
	protected static $key="contents/validators/";
	
	/**
	 * Registers a validator type for using with @validator annotation
	 * @param string $type
	 * @param string $validatorClass
	 */
	public static function registerType($type,$validatorClass){
		self::$validatorTypes[$type]=$validatorClass;
	}
	
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
	
	protected static function store($model,$validators){
		CacheManager::$cache->store(self::getModelCacheKey($model), $validators);
	}
	
	protected static function fetch($model){
		$key=self::getModelCacheKey($model);
		if(CacheManager::$cache->exists($key)){
			return CacheManager::$cache->fetch($key);
		}
		return [];
	}
	
	protected static function getGroupValidators(array $validators,$group){
		$result=[];
		foreach ($validators as $member=>$validators){
			$filteredValidators=self::getGroupMemberValidators($validators, $group);
			if(sizeof($filteredValidators)){
				$result[$member]=$filteredValidators;
			}
		}
		return $result;
	}
	
	protected static function getGroupMemberValidators(array $validators,$group){
		$result=[];
		foreach ($validators as $validator){
			if(isset($validator["group"]) && $validator["group"]===$group){
				$result[]=$validator;
			}
		}
		return $result;
	}
	
	/**
	 * Validates an instance
	 * @param object $instance
	 * @return \Ubiquity\contents\validation\validators\ConstraintViolation[]
	 */
	public static function validate($instance){
		return self::validate_($instance,self::fetch(get_class($instance)));
	}
	
	/**
	 * Validates an instance using a group of validators
	 * @param object $instance
	 * @param string $group
	 * @return \Ubiquity\contents\validation\validators\ConstraintViolation[]
	 */
	public static function validateGroup($instance,$group){
		$members=self::getGroupValidators(self::fetch(get_class($instance)), $group);
		return self::validate_($instance,$members);
	}
	
	protected static function validate_($instance,$members){
		$result=[];
		foreach ($members as $member=>$validators){
			$accessor="get".ucfirst($member);
			if(method_exists($instance, $accessor)){
				foreach ($validators as $validator){
					$validatorInstance=self::getValidatorInstance($validator["type"]);
					if($validatorInstance!==false){
						$valid=$validatorInstance->validate_($instance->$accessor(),$member,$validator["constraints"],@$validator["severity"],@$validator["message"]);
						if($valid!==true){
							$result[]=$valid;
						}
					}
				}
			}
		}
		return $result;
	}
	
	protected static function getModelCacheKey($classname){
		return self::$key.\str_replace("\\", DIRECTORY_SEPARATOR, $classname);
	}
	
	protected static function getValidatorInstance($type){
		if(!isset(self::$validatorInstances[$type])){
			if(isset(self::$validatorTypes[$type])){
				$class=self::$validatorTypes[$type];
				self::$validatorInstances[$type]=new $class();
			}else{
				Logger::warn("validation", "Validator ".$type." does not exists!");
				return false;
			}
		}
		return self::$validatorInstances[$type];
	}
}


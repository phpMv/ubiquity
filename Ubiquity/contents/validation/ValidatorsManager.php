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
use Ubiquity\contents\validation\validators\strings\IpValidator;
use Ubiquity\contents\validation\validators\comparison\RangeValidator;
use Ubiquity\contents\validation\validators\comparison\GreaterThanOrEqualValidator;
use Ubiquity\contents\validation\validators\comparison\LessThanOrEqualValidator;
use Ubiquity\contents\validation\validators\dates\DateValidator;
use Ubiquity\contents\validation\validators\dates\DateTimeValidator;
use Ubiquity\contents\validation\validators\dates\TimeValidator;
use Ubiquity\contents\validation\validators\basic\IsBooleanValidator;

/**
 * Validators manager
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 */
class ValidatorsManager {
	protected static $instanceValidators=[];
	public static $validatorTypes=[
			"notNull"=>NotNullValidator::class,
			"isNull"=>IsNullValidator::class,
			"notEmpty"=>NotEmptyValidator::class,
			"isEmpty"=>IsEmptyValidator::class,
			"isTrue"=>IsTrueValidator::class,
			"isFalse"=>IsFalseValidator::class,
			"isBool"=>IsBooleanValidator::class,
			"equals"=>EqualsValidator::class,
			"type"=>TypeValidator::class,
			"greaterThan"=>GreaterThanValidator::class,
			"greaterThanOrEqual"=>GreaterThanOrEqualValidator::class,
			"lessThan"=>LessThanValidator::class,
			"lessThanOrEqual"=>LessThanOrEqualValidator::class,
			"length"=>LengthValidator::class,
			"id"=>IdValidator::class,
			"regex"=>RegexValidator::class,
			"email"=>EmailValidator::class,
			"url"=>UrlValidator::class,
			"ip"=>IpValidator::class,
			"range"=>RangeValidator::class,
			"date"=>DateValidator::class,
			"dateTime"=>DateTimeValidator::class,
			"time"=>TimeValidator::class
			
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
	 * Validates an array of objects
	 * @param array $instances
	 * @return \Ubiquity\contents\validation\validators\ConstraintViolation[]
	 */
	public static function validateInstances($instances){
		if(sizeof($instances)>0){
			$instance=reset($instances);
			$class=get_class($instance);
			$members=self::fetch($class);
			self::initInstancesValidators($instance, $members);
			return self::validateInstances_($instances, self::$instanceValidators[$class]);
		}
		return [];
	}
	
	protected static function validateInstances_($instances,$members){
		$result=[];
		foreach ($instances as $instance){
			foreach ($members as $member=>$validators){
				$accessor="get".ucfirst($member);
				foreach ($validators as $validator){
					$valid=$validator->validate_($instance->$accessor());
					if($valid!==true){
						$result[]=$valid;
					}
				}
			}
		}
		return $result;
	}
	
	/**
	 * Validates an array of objects using a group of validators
	 * @param array $instances
	 * @param string $group
	 * @return \Ubiquity\contents\validation\validators\ConstraintViolation[]
	 */
	public static function validateInstancesGroup($instances,$group){
		if(sizeof($instances)>0){
			$instance=reset($instances);
			$class=get_class($instance);
			$members=self::fetch($class);
			$members=self::getGroupValidators($members, $group);
			self::initInstancesValidators($instance, $members);
			return self::validateInstances_($instances,self::$instanceValidators[$class]);
		}
		return [];
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
						$validatorInstance->setValidationParameters($member,$validator["constraints"],@$validator["severity"],@$validator["message"]);
						$valid=$validatorInstance->validate_($instance->$accessor());
						if($valid!==true){
							$result[]=$valid;
						}
					}
				}
			}
		}
		return $result;
	}
	
	protected static function initInstancesValidators($instance,$members,$group=null){
		$class=get_class($instance);
		foreach ($members as $member=>$validators){
			$accessor="get".ucfirst($member);
			if(method_exists($instance, $accessor)){
				foreach ($validators as $validator){
					$validatorInstance=self::getValidatorInstance($validator["type"]);
					if($validatorInstance!==false){
						$validatorInstance->setValidationParameters($member,$validator["constraints"],@$validator["severity"],@$validator["message"]);
						if(!isset($group) || (isset($validator["group"]) && $validator["group"]===$group)){
							self::$instanceValidators[$class][$member][]=$validatorInstance;
						}
					}
				}
			}
		}
	}
	
	protected static function getModelCacheKey($classname){
		return self::$key.\str_replace("\\", DIRECTORY_SEPARATOR, $classname);
	}
	
	protected static function getValidatorInstance($type){
		if(isset(self::$validatorTypes[$type])){
			$class=self::$validatorTypes[$type];
			return new $class();
		}else{
			Logger::warn("validation", "Validator ".$type." does not exists!");
			return false;
		}
	}
}


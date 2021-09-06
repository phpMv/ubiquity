<?php

namespace Ubiquity\contents\validation;

use Ubiquity\cache\CacheManager;
use Ubiquity\cache\objects\SessionCache;
use Ubiquity\contents\validation\traits\ValidatorsManagerInitTrait;
use Ubiquity\contents\validation\validators\basic\IsBooleanValidator;
use Ubiquity\contents\validation\validators\basic\IsEmptyValidator;
use Ubiquity\contents\validation\validators\basic\IsFalseValidator;
use Ubiquity\contents\validation\validators\basic\IsNullValidator;
use Ubiquity\contents\validation\validators\basic\IsTrueValidator;
use Ubiquity\contents\validation\validators\basic\NotEmptyValidator;
use Ubiquity\contents\validation\validators\basic\NotNullValidator;
use Ubiquity\contents\validation\validators\basic\TypeValidator;
use Ubiquity\contents\validation\validators\comparison\EqualsValidator;
use Ubiquity\contents\validation\validators\comparison\GreaterThanOrEqualValidator;
use Ubiquity\contents\validation\validators\comparison\GreaterThanValidator;
use Ubiquity\contents\validation\validators\comparison\LessThanOrEqualValidator;
use Ubiquity\contents\validation\validators\comparison\LessThanValidator;
use Ubiquity\contents\validation\validators\comparison\MatchWithValidator;
use Ubiquity\contents\validation\validators\comparison\RangeValidator;
use Ubiquity\contents\validation\validators\dates\DateTimeValidator;
use Ubiquity\contents\validation\validators\dates\DateValidator;
use Ubiquity\contents\validation\validators\dates\TimeValidator;
use Ubiquity\contents\validation\validators\multiples\IdValidator;
use Ubiquity\contents\validation\validators\multiples\LengthValidator;
use Ubiquity\contents\validation\validators\strings\EmailValidator;
use Ubiquity\contents\validation\validators\strings\IpValidator;
use Ubiquity\contents\validation\validators\strings\RegexValidator;
use Ubiquity\contents\validation\validators\strings\UrlValidator;
use Ubiquity\log\Logger;
use Ubiquity\contents\validation\traits\ValidatorsManagerCacheTrait;

/**
 * Validators manager
 *
 * Ubiquity\contents\validation$ValidatorsManager
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.4
 *
 */
class ValidatorsManager {
	use ValidatorsManagerInitTrait,ValidatorsManagerCacheTrait;
	protected static $instanceValidators = [ ];

	public static function start() {
		self::$cache = new SessionCache();
	}
	public static $validatorTypes = [
										'notNull' => NotNullValidator::class,
										'isNull' => IsNullValidator::class,
										'notEmpty' => NotEmptyValidator::class,
										'isEmpty' => IsEmptyValidator::class,
										'isTrue' => IsTrueValidator::class,
										'isFalse' => IsFalseValidator::class,
										'isBool' => IsBooleanValidator::class,
										'equals' => EqualsValidator::class,
										'type' => TypeValidator::class,
										'greaterThan' => GreaterThanValidator::class,
										'greaterThanOrEqual' => GreaterThanOrEqualValidator::class,
										'lessThan' => LessThanValidator::class,
										'lessThanOrEqual' => LessThanOrEqualValidator::class,
										'length' => LengthValidator::class,
										'id' => IdValidator::class,
										'regex' => RegexValidator::class,
										'email' => EmailValidator::class,
										'url' => UrlValidator::class,
										'ip' => IpValidator::class,
										'range' => RangeValidator::class,
										'date' => DateValidator::class,
										'dateTime' => DateTimeValidator::class,
										'time' => TimeValidator::class,
										'match'=>MatchWithValidator::class
	];



	public static function getCacheInfo($model) {
		return self::fetch( $model );
	}

	protected static function getGroupArrayValidators(array $validators, $group) {
		$result = [ ];
		foreach ( $validators as $member => $validators ) {
			$filteredValidators = self::getGroupMemberValidators ( $validators, $group );
			if (\count ( $filteredValidators ) > 0) {
				$result [$member] = $filteredValidators;
			}
		}
		return $result;
	}

	protected static function getGroupMemberValidators(array $validators, $group) {
		$result = [ ];
		foreach ( $validators as $validator ) {
			if (isset ( $validator ['group'] ) && $validator ['group'] === $group) {
				$result [] = $validator;
			}
		}
		return $result;
	}


	/**
	 * Validates an instance
	 *
	 * @param object $instance
	 * @param string $group
	 * @param array $excludedValidators
	 * @return \Ubiquity\contents\validation\validators\ConstraintViolation[]
	 */
	public static function validate($instance, $group = '', $excludedValidators = [ ]) {
		$class = \get_class ( $instance );
		$cache = self::getClassCacheValidators ( $class, $group );
		if ($cache !== false) {
			return self::validateFromCache_ ( $instance, $cache, $excludedValidators );
		}
		$members = self::fetch ( $class );
		if ($group !== '') {
			$members = self::getGroupArrayValidators ( $members, $group );
		}
		return self::validate_ ( $instance, $members, $excludedValidators );
	}

	/**
	 * Returns an array of UI rules for Javascript validation.
	 * @param $instance
	 * @param string $group
	 * @param array $excludedValidators
	 * @return array
	 */
	public static function getUIConstraints($instance, $group = '', $excludedValidators = [ ]) {
		$class = \get_class ( $instance );
		$cache = self::getClassCacheValidators ( $class, $group );
		if ($cache !== false) {
			return self::getUIConstraintsFromCache_ ( $instance, $cache, $excludedValidators );
		}
		$members = self::fetch ( $class );
		if ($group !== '') {
			$members = self::getGroupArrayValidators ( $members, $group );
		}
		return self::getUIConstraints_ ( $instance, $members, $excludedValidators );
	}

	/**
	 * Validates an array of objects
	 *
	 * @param array $instances
	 * @param string $group
	 * @return \Ubiquity\contents\validation\validators\ConstraintViolation[]
	 */
	public static function validateInstances($instances, $group = '') {
		if (\count ( $instances ) > 0) {
			$instance = \current ( $instances );
			$class = \get_class ( $instance );
			$cache = self::getClassCacheValidators ( $class, $group );
			if ($cache === false) {
				$members = self::fetch ( $class );
				self::initInstancesValidators ( $instance, $members, $group );
				$cache = self::$instanceValidators [$class];
			}
			return self::validateInstances_ ( $instances, $cache );
		}
		return [ ];
	}

	protected static function validateInstances_($instances, $members) {
		$result = [ ];
		foreach ( $instances as $instance ) {
			foreach ( $members as $accessor => $validators ) {
				foreach ( $validators as $validator ) {
					$valid = $validator->validate_ ( $instance->$accessor () );
					if ($valid !== true) {
						$result [] = $valid;
					}
				}
			}
		}
		return $result;
	}

	protected static function validate_($instance, $members, $excludedValidators = [ ]) {
		$result = [ ];
		foreach ( $members as $member => $validators ) {
			$accessor = 'get' . \ucfirst ( $member );
			if (\method_exists ( $instance, $accessor )) {
				foreach ( $validators as $validator ) {
					$typeV = $validator ['type'];
					if (! isset ( $excludedValidators [$typeV] )) {
						$validatorInstance = self::getValidatorInstance ( $typeV );
						if ($validatorInstance !== false) {
							$validatorInstance->setValidationParameters ( $member, $validator ['constraints'] ?? [ ], $validator ['severity'] ?? null, $validator ['message'] ?? null);
							$valid = $validatorInstance->validate_ ( $instance->$accessor () );
							if ($valid !== true) {
								$result [] = $valid;
							}
						}
					}
				}
			}
		}
		return $result;
	}

	protected static function getUIConstraints_($instance, $members, $excludedValidators = [ ]) {
		$result = [ ];
		foreach ( $members as $member => $validators ) {
			$result [$member]=[];
			$accessor = 'get' . \ucfirst ( $member );
			if (\method_exists ( $instance, $accessor )) {
				foreach ( $validators as $validator ) {
					$typeV = $validator ['type'];
					if (! isset ( $excludedValidators [$typeV] )) {
						$validatorInstance = self::getValidatorInstance ( $typeV );
						if ($validatorInstance !== false) {
							$validatorInstance->setValidationParameters ( $member, $validator ['constraints'] ?? [ ], $validator ['severity'] ?? null, $validator ['message'] ?? null);
							$result [$member] = \array_merge_recursive($result[$member],$validatorInstance->asUI ());
						}
					}
				}
			}
		}
		return $result;
	}
}


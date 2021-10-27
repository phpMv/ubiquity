<?php

namespace Ubiquity\contents\validation\traits;

use Ubiquity\cache\CacheManager;
use Ubiquity\controllers\Startup;
use Ubiquity\contents\validation\ValidationModelParser;

/**
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @property array $validatorTypes
 */
trait ValidatorsManagerInitTrait {

	/**
	 * Parses models and save validators in cache
	 * to use in dev only
	 *
	 * @param array $config
	 * @param string $databaseOffset
	 */
	public static function initModelsValidators(&$config, $databaseOffset = 'default') {
		$models = CacheManager::getModels ( $config, true, $databaseOffset );
		foreach ( $models as $model ) {
			self::initClassValidators ( $model );
		}
	}

	/**
	 *
	 * Parses a class and save validators in cache
	 * to use in dev only
	 *
	 * @param string $class
	 */
	public static function initClassValidators($class) {
		$parser = new ValidationModelParser();
		$parser->parse ( $class );
		$validators = $parser->getValidators ();
		if (\count ( $validators ) > 0) {
			self::store ( $class, $validators );
		}
	}

	/**
	 * Parses a class and save validators in cache
	 *
	 * @param string $class
	 */
	public static function addClassValidators($class) {
		$config = Startup::getConfig ();
		CacheManager::start ( $config );
		self::initClassValidators ( $class );
	}

	/**
	 * Registers a validator type for using with @validator annotation
	 *
	 * @param string $type
	 * @param string $validatorClass
	 */
	public static function registerType($type, $validatorClass) {
		self::$validatorTypes [$type] = $validatorClass;
	}
}


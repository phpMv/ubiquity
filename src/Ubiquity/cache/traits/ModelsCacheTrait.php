<?php

/**
 * Cache traits
 */
namespace Ubiquity\cache\traits;

use Ubiquity\orm\parser\ModelParser;
use Ubiquity\cache\ClassUtils;
use Ubiquity\contents\validation\ValidatorsManager;
use Ubiquity\orm\parser\Reflexion;
use Ubiquity\utils\base\UArray;
use Ubiquity\exceptions\UbiquityException;
use Ubiquity\orm\OrmUtils;

/**
 *
 * Ubiquity\cache\traits$ModelsCacheTrait
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.3
 * @property \Ubiquity\cache\system\AbstractDataCache $cache
 */
trait ModelsCacheTrait {

	abstract protected static function _getFiles(&$config, $type, $silent = false);

	public static function createOrmModelCache($classname) {
		$key = self::getModelCacheKey ( $classname );
		if (isset ( self::$cache )) {
			$p = new ModelParser ();
			$p->parse ( $classname );
			self::$cache->store ( $key, $p->__toString (), 'models' );
			return self::$cache->fetch ( $key );
		}
	}

	public static function getOrmModelCache($classname) {
		return self::$cache->fetch ( self::getModelCacheKey ( $classname ) );
	}

	public static function getModelCacheKey($classname) {
		return \str_replace ( "\\", \DS, $classname );
	}

	public static function modelCacheExists($classname) {
		$key = self::getModelCacheKey ( $classname );
		if (isset ( self::$cache ))
			return self::$cache->exists ( $key );
		return false;
	}

	public static function initModelsCache(&$config, $forChecking = false, $silent = false) {
		$modelsDb = [ ];
		$files = self::getModelsFiles ( $config, $silent );
		foreach ( $files as $file ) {
			if (is_file ( $file )) {
				$model = ClassUtils::getClassFullNameFromFile ( $file );
				if (! $forChecking) {
					self::createOrmModelCache ( $model );
					$db = 'default';
					$ret = Reflexion::getAnnotationClass ( $model, '@database' );
					if (\sizeof ( $ret ) > 0) {
						$db = $ret [0]->name;
						if (! isset ( $config ['database'] [$db] )) {
							throw new UbiquityException ( $db . ' connection is not defined in config array' );
						}
					}
					$modelsDb [$model] = $db;
					ValidatorsManager::initClassValidators ( $model );
				}
			}
		}
		if (! $forChecking) {
			self::$cache->store ( 'models' . \DS . '_modelsDatabases', 'return ' . UArray::asPhpArray ( $modelsDb, 'array' ) . ';', 'models' );
		}
		if (! $silent) {
			echo "Models cache reset\n";
		}
	}

	/**
	 * Checks if the models cache is up to date
	 *
	 * @param array $config
	 * @return boolean|array
	 */
	public static function modelsCacheUpdated(&$config) {
		$result = false;
		$files = self::getModelsFiles ( $config, true );
		foreach ( $files as $file ) {
			if (is_file ( $file )) {
				$model = ClassUtils::getClassFullNameFromFile ( $file );
				$p = new ModelParser ();
				$p->parse ( $model );
				if (! self::modelCacheExists ( $model ) || self::getOrmModelCache ( $model ) != $p->asArray ()) {
					$result [$model] = true;
				}
			}
		}
		return $result;
	}

	/**
	 * Returns an array of files corresponding to models
	 *
	 * @param array $config
	 * @param boolean $silent
	 * @return array
	 */
	public static function getModelsFiles(&$config, $silent = false) {
		return self::_getFiles ( $config, 'models', $silent );
	}

	/**
	 * Returns an array of the models class names
	 *
	 * @param array $config
	 * @param boolean $silent
	 * @return string[]
	 */
	public static function getModels(&$config, $silent = false, $databaseOffset = 'default') {
		$result = [ ];
		$files = self::getModelsFiles ( $config, $silent );
		foreach ( $files as $file ) {
			$className = ClassUtils::getClassFullNameFromFile ( $file );
			$db = 'default';
			$ret = Reflexion::getAnnotationClass ( $className, '@database' );
			if (\sizeof ( $ret ) > 0) {
				$db = $ret [0]->name;
			}
			if ($db === $databaseOffset) {
				$result [] = $className;
			}
		}
		return $result;
	}

	public static function getModelsDatabases() {
		if (self::$cache->exists ( 'models/_modelsDatabases' )) {
			return self::$cache->fetch ( 'models/_modelsDatabases' );
		}
		return [ ];
	}

	/**
	 * Preloads models metadatas.
	 * To use only with async servers (Swoole, Workerman)
	 *
	 * @param array $config
	 * @param string $offset
	 * @param ?array $models
	 */
	public static function warmUpModels(&$config, $offset = 'default', $models = null) {
		$models = $models ?? self::getModels ( $config, true, $offset );
		foreach ( $models as $model ) {
			OrmUtils::getModelMetadata ( $model );
			Reflexion::getPropertiesAndValues ( new $model () );
		}
	}
}

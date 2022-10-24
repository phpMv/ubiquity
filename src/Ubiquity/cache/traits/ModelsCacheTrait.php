<?php

/**
 * Cache traits
 */
namespace Ubiquity\cache\traits;

use Ubiquity\orm\parser\ModelParser;
use Ubiquity\cache\ClassUtils;
use Ubiquity\contents\validation\ValidatorsManager;
use Ubiquity\orm\parser\Reflexion;
use Ubiquity\exceptions\UbiquityException;
use Ubiquity\orm\OrmUtils;

/**
 *
 * Ubiquity\cache\traits$ModelsCacheTrait
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.5
 * @staticvar \Ubiquity\cache\system\AbstractDataCache $cache
 */
trait ModelsCacheTrait {

	abstract protected static function _getFiles(array &$config, string $type, bool $silent = false): array;
	abstract protected static function _getAllFiles(array &$config, string $type, bool $silent = false): array ;

	private static $modelsDatabaseKey = 'models' . \DIRECTORY_SEPARATOR . '_modelsDatabases';

	public static function createOrmModelCache(string $classname) {
		$key = self::getModelCacheKey ( $classname );
		if (isset ( self::$cache )) {
			$p = new ModelParser ();
			$p->parse ( $classname );
			self::$cache->store ( $key, $p->asArray (), 'models' );
			return self::$cache->fetch ( $key );
		}
	}

	public static function getOrmModelCache(string $classname) {
		return self::$cache->fetch ( self::getModelCacheKey ( $classname ) );
	}

	public static function getModelCacheKey(string $classname): string {
		return \str_replace ( "\\", \DS, $classname );
	}

	public static function modelCacheExists(string $classname): bool {
		$key = self::getModelCacheKey ( $classname );
		if (isset ( self::$cache ))
			return self::$cache->exists ( $key );
		return false;
	}

	public static function initModelsCache(array &$config, bool $forChecking = false, bool $silent = false): void {
		$modelsDb = [ ];
		$files = self::getAllModelsFiles( $config, $silent );
		foreach ( $files as $file ) {
			if (\is_file ( $file )) {
				$model = ClassUtils::getClassFullNameFromFile ( $file );
				if($model!=null) {
					if (!\class_exists($model)) {
						if (\file_exists($file)) {
							include $file;
						}
					}
					if (!$forChecking) {
						self::createOrmModelCache($model);
						$db = 'default';
						$ret = Reflexion::getAnnotationClass($model, 'database');
						if (\count($ret) > 0) {
							$db = $ret [0]->name;
							if (!isset ($config ['database'] [$db])) {
								throw new UbiquityException ($db . ' connection is not defined in config array');
							}
						}
						$modelsDb [$model] = $db;
						ValidatorsManager::initClassValidators($model);
					}
				}
			}
		}
		if (! $forChecking) {
			self::$cache->store ( self::$modelsDatabaseKey, $modelsDb, 'models' );
		}
		if (! $silent) {
			echo "Models cache reset\n";
		}
	}

	/**
	 * Checks if the models cache is up to date
	 *
	 * @param array $config
	 * @return array
	 */
	public static function modelsCacheUpdated(array &$config): array {
		$result = [];
		$files = self::getModelsFiles ( $config, true );
		foreach ( $files as $file ) {
			if (\is_file ( $file )) {
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
	public static function getModelsFiles(array &$config, bool $silent = false): array {
		return self::_getFiles ( $config, 'models', $silent );
	}

	/**
	 * Returns an array of all model files
	 *
	 * @param array $config
	 * @param boolean $silent
	 * @return array
	 */
	public static function getAllModelsFiles(array &$config, bool $silent = false): array {
		return self::_getAllFiles ( $config, 'models', $silent );
	}

	/**
	 * Returns an array of the models class names
	 *
	 * @param array $config
	 * @param boolean $silent
	 * @param ?string $databaseOffset
	 * @return string[]
	 */
	public static function getModels(array &$config, bool $silent = false, string $databaseOffset = 'default'): array {
		$result = [];
		$files = self::getModelsFiles($config, $silent);
		foreach ($files as $file) {
			$className = ClassUtils::getClassFullNameFromFile($file);
			if($className!=null) {
				if (\class_exists($className, true)) {
					$db = 'default';
					$ret = Reflexion::getAnnotationClass($className, 'database');
					if (\count($ret) > 0) {
						$db = $ret[0]->name;
					}
					if ($databaseOffset == null || $db === $databaseOffset) {
						$result[] = $className;
					}
				}
			}
		}
		return $result;
	}

	public static function getModelsNamespace(string $databaseOffset='default'): ?string {
		$modelsDatabases=self::getModelsDatabases();
		foreach ($modelsDatabases as $model=>$offset){
			if($offset===$databaseOffset){
				$rc=new \ReflectionClass($model);
				return $rc->getNamespaceName();
			}
		}
		return null;
	}

	public static function getModelsDatabases(): array {
		if (self::$cache->exists ( self::$modelsDatabaseKey )) {
			return self::$cache->fetch ( self::$modelsDatabaseKey );
		}
		return [ ];
	}

	public static function storeModelsDatabases(array $modelsDatabases): void {
		self::$cache->store ( self::$modelsDatabaseKey, $modelsDatabases, 'models' );
	}

	public static function getDatabases(): array {
		return \array_keys(\array_flip(self::getModelsDatabases()));
	}

	/**
	 * Preloads models metadatas.
	 * To use only with async servers (Swoole, Workerman)
	 *
	 * @param array $config
	 * @param string $offset
	 * @param ?array $models
	 */
	public static function warmUpModels(array &$config, string $offset = 'default', ?array $models = null): void {
		$models ??= self::getModels ( $config, true, $offset );
		foreach ( $models as $model ) {
			OrmUtils::getModelMetadata ( $model );
			Reflexion::getPropertiesAndValues ( new $model () );
		}
	}
}

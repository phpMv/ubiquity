<?php
namespace Ubiquity\cache\traits;

use Ubiquity\orm\parser\ModelParser;
use Ubiquity\cache\ClassUtils;

/**
 * @author jc
 * @static array $cache
 */
trait ModelsCacheTrait{
	abstract protected static function _getFiles(&$config,$type,$silent=false);

	public static function createOrmModelCache($classname) {
		$key=self::getModelCacheKey($classname);
		if(isset(self::$cache)){
			if (!self::$cache->exists($key)) {
				$p=new ModelParser();
				$p->parse($classname);
				self::$cache->store($key, $p->__toString(),'models');
			}
			return self::$cache->fetch($key);
		}
	}

	public static function getOrmModelCache($classname) {
		return self::$cache->fetch(self::getModelCacheKey($classname));
	}

	public static function getModelCacheKey($classname){
		return \str_replace("\\", DIRECTORY_SEPARATOR, $classname);
	}

	public static function modelCacheExists($classname){
		$key=self::getModelCacheKey($classname);
		if(isset(self::$cache))
			return self::$cache->exists($key);
			return false;
	}

	public static function initModelsCache(&$config,$forChecking=false,$silent=false) {
		$files=self::getModelsFiles($config,$silent);
		foreach ( $files as $file ) {
			if (is_file($file)) {
				$model=ClassUtils::getClassFullNameFromFile($file);
				if(!$forChecking){
					self::createOrmModelCache($model);
				}
			}
		}
		if(!$silent){
			echo "Models cache reset\n";
		}
	}

	public static function getModelsFiles(&$config,$silent=false){
		return self::_getFiles($config, "models",$silent);
	}

	public static function getModels(&$config,$silent=false){
		$result=[];
		$files=self::getModelsFiles($config,$silent);
		foreach ($files as $file){
			$result[]=ClassUtils::getClassFullNameFromFile($file);
		}
		return $result;
	}

}

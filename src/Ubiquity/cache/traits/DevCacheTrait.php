<?php
namespace Ubiquity\cache\traits;

use Ubiquity\utils\base\UFileSystem;
use mindplay\annotations\AnnotationCache;
use mindplay\annotations\AnnotationManager;
use mindplay\annotations\Annotations;

/**
 * To be Used in dev mode, not in production
 * Ubiquity\cache\traits$DevCacheTrait
 * This class is part of Ubiquity
 *
 * @author jc
 * @version 1.0.1
 *
 * @property string $cacheDirectory
 */
trait DevCacheTrait {

	/**
	 *
	 * @var array array of annotations name/class
	 */
	protected static $registry;

	abstract protected static function getCacheInstance(&$config, $cacheDirectory, $postfix);

	abstract protected static function initRestCache(&$config, $silent = false);

	abstract protected static function initRouterCache(&$config, $silent = false);

	abstract public static function initModelsCache(&$config, $forChecking = false, $silent = false);

	private static function initialGetCacheDirectory(&$config) {
		return $config['cache']['directory'] ??= 'cache' . \DS;
	}

	/**
	 * Starts the cache in dev mode, for generating the other caches
	 * Do not use in production
	 *
	 * @param array $config
	 */
	public static function start(&$config) {
		self::$registry = [
			'id' => 'Ubiquity\annotations\IdAnnotation',
			'manyToOne' => 'Ubiquity\annotations\ManyToOneAnnotation',
			'oneToMany' => 'Ubiquity\annotations\OneToManyAnnotation',
			'manyToMany' => 'Ubiquity\annotations\ManyToManyAnnotation',
			'joinColumn' => 'Ubiquity\annotations\JoinColumnAnnotation',
			'table' => 'Ubiquity\annotations\TableAnnotation',
			'database' => 'Ubiquity\annotations\DatabaseAnnotation',
			'transient' => 'Ubiquity\annotations\TransientAnnotation',
			'column' => 'Ubiquity\annotations\ColumnAnnotation',
			'validator' => 'Ubiquity\annotations\ValidatorAnnotation',
			'transformer' => 'Ubiquity\annotations\TransformerAnnotation',
			'joinTable' => 'Ubiquity\annotations\JoinTableAnnotation',
			'requestMapping' => 'Ubiquity\annotations\router\RouteAnnotation',
			'route' => 'Ubiquity\annotations\router\RouteAnnotation',
			'get' => 'Ubiquity\annotations\router\GetAnnotation',
			'getMapping' => 'Ubiquity\annotations\router\GetAnnotation',
			'post' => 'Ubiquity\annotations\router\PostAnnotation',
			'postMapping' => 'Ubiquity\annotations\router\PostAnnotation',
			'put' => 'Ubiquity\annotations\router\PutAnnotation',
			'putMapping' => 'Ubiquity\annotations\router\PutAnnotation',
			'patch' => 'Ubiquity\annotations\router\PatchAnnotation',
			'patchMapping' => 'Ubiquity\annotations\router\PatchAnnotation',
			'delete' => 'Ubiquity\annotations\router\DeleteAnnotation',
			'deleteMapping' => 'Ubiquity\annotations\router\DeleteAnnotation',
			'options' => 'Ubiquity\annotations\router\OptionsAnnotation',
			'optionsMapping' => 'Ubiquity\annotations\router\OptionsAnnotation',
			'var' => 'mindplay\annotations\standard\VarAnnotation',
			'yuml' => 'Ubiquity\annotations\YumlAnnotation',
			'rest' => 'Ubiquity\annotations\rest\RestAnnotation',
			'authorization' => 'Ubiquity\annotations\rest\AuthorizationAnnotation',
			'injected' => 'Ubiquity\annotations\di\InjectedAnnotation',
			'autowired' => 'Ubiquity\annotations\di\AutowiredAnnotation'
		];
		self::$cacheDirectory = self::initialGetCacheDirectory($config);
		$cacheDirectory = \ROOT . \DS . self::$cacheDirectory;
		Annotations::$config['cache'] = new AnnotationCache($cacheDirectory . '/annotations');
		self::register(Annotations::getManager());
		self::getCacheInstance($config, $cacheDirectory, '.cache')->init();
	}

	/**
	 *
	 * @param array $nameClasses
	 *        	an array of name=>class annotations
	 */
	public static function registerAnnotations(array $nameClasses): void {
		$annotationManager = Annotations::getManager();
		foreach ($nameClasses as $name => $class) {
			self::$registry[$name] = $class;
			$annotationManager->registry[$name] = $class;
		}
	}

	protected static function register(AnnotationManager $annotationManager) {
		$annotationManager->registry = \array_merge($annotationManager->registry, self::$registry);
	}

	/**
	 * Checks the existence of cache subdirectories and returns an array of cache folders
	 *
	 * @param array $config
	 * @param boolean $silent
	 * @return string[]
	 */
	public static function checkCache(&$config, $silent = false) {
		$dirs = self::getCacheDirectories($config, $silent);
		foreach ($dirs as $dir) {
			self::safeMkdir($dir);
		}
		return $dirs;
	}

	/**
	 * Returns an associative array of cache folders (annotations, models, controllers, queries, views, seo, git, contents)
	 *
	 * @param array $config
	 * @param boolean $silent
	 * @return string[]
	 */
	public static function getCacheDirectories(&$config, $silent = false) {
		$cacheDirectory = self::initialGetCacheDirectory($config);
		$rootDS = \ROOT . \DS;
		if (! $silent) {
			echo "cache directory is " . UFileSystem::cleanPathname($rootDS . $cacheDirectory) . "\n";
		}
		$cacheDirectory = $rootDS . $cacheDirectory . \DS;
		$modelsDir = str_replace("\\", \DS, $config['mvcNS']['models']);
		$controllersDir = str_replace("\\", \DS, $config['mvcNS']['controllers']);
		$annotationCacheDir = $cacheDirectory . 'annotations';
		$modelsCacheDir = $cacheDirectory . $modelsDir;
		$queriesCacheDir = $cacheDirectory . 'queries';
		$controllersCacheDir = $cacheDirectory . $controllersDir;
		$viewsCacheDir = $cacheDirectory . 'views';
		$seoCacheDir = $cacheDirectory . 'seo';
		$gitCacheDir = $cacheDirectory . 'git';
		$contentsCacheDir = $cacheDirectory . 'contents';
		return [
			'annotations' => $annotationCacheDir,
			'models' => $modelsCacheDir,
			'controllers' => $controllersCacheDir,
			'queries' => $queriesCacheDir,
			'views' => $viewsCacheDir,
			'seo' => $seoCacheDir,
			'git' => $gitCacheDir,
			'contents' => $contentsCacheDir
		];
	}

	private static function safeMkdir($dir) {
		if (! \is_dir($dir))
			return \mkdir($dir, 0777, true);
	}

	/**
	 * Deletes files from a cache type
	 *
	 * @param array $config
	 * @param string $type
	 */
	public static function clearCache(&$config, $type = 'all') {
		$cacheDirectories = self::checkCache($config);
		$cacheDirs = [
			'annotations',
			'controllers',
			'models',
			'queries',
			'views',
			'contents'
		];
		foreach ($cacheDirs as $typeRef) {
			self::_clearCache($cacheDirectories, $type, $typeRef);
		}
	}

	private static function _clearCache($cacheDirectories, $type, $typeRef) {
		if ($type === 'all' || $type === $typeRef)
			UFileSystem::deleteAllFilesFromFolder($cacheDirectories[$typeRef]);
	}

	/**
	 *
	 * @param array $config
	 * @param string $type
	 * @param boolean $silent
	 */
	public static function initCache(&$config, $type = 'all', $silent = false) {
		self::checkCache($config, $silent);
		self::start($config);
		if ($type === 'all' || $type === 'models') {
			self::initModelsCache($config, false, $silent);
		}
		if ($type === 'all' || $type === 'controllers') {
			if (\class_exists('\\Ubiquity\\security\\acl\\AclManager')) {
				\Ubiquity\security\acl\AclManager::registerAnnotations($config);
			}
			self::initRouterCache($config, $silent);
		}
		if ($type === 'all' || $type === 'rest') {
			self::initRestCache($config, $silent);
		}
	}
}


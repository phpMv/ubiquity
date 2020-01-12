<?php

namespace Ubiquity\cache;

use Ubiquity\cache\preloading\PreloaderInternalTrait;

/**
 * Ubiquity\cache$Preloader
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.1
 *
 */
class Preloader {
	use PreloaderInternalTrait;

	/**
	 * Creates a new loader instance for this application.
	 *
	 * @param string $appRoot The app root
	 */
	public function __construct($appRoot) {
		$this->vendorDir = $appRoot . './../vendor/';
		$this->loader = require $this->vendorDir . 'autoload.php';
	}

	/**
	 * Adds paths to be scanned during preloading.
	 *
	 * @param string ...$paths
	 * @return Preloader
	 */
	public function paths(string ...$paths): Preloader {
		foreach ( $paths as $path ) {
			$this->addDir ( $path );
		}
		return $this;
	}

	/**
	 * Adds namespaces to exclude from preloading.
	 *
	 * @param string ...$names
	 * @return Preloader
	 */
	public function exclude(string ...$names): Preloader {
		$this->excludeds = \array_merge ( $this->excludeds, $names );
		return $this;
	}

	/**
	 * Adds a class to preload.
	 *
	 * @param string $class
	 * @return bool
	 */
	public function addClass(string $class): bool {
		if (! $this->isExcluded ( $class )) {
			if (! isset ( $this->classes [$class] )) {
				$path = $this->getPathFromClass ( $class );
				if (isset ( $path )) {
					$this->classes [$class] = $path;
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Adds an array of classes to preload.
	 *
	 * @param array $classes
	 */
	public function addClasses(array $classes) {
		foreach ( $classes as $class ) {
			$this->addClass ( $class );
		}
	}

	/**
	 * Preload all added classes.
	 */
	public function load() {
		foreach ( $this->classes as $class => $file ) {
			if (! $this->isExcluded ( $class )) {
				$this->loadClass ( $class, $file );
			}
		}
	}

	/**
	 * Returns a generated associative array of classes to preload (key: class, value: file).
	 *
	 * @return array
	 */
	public function generateClassesFiles(): array {
		$ret = [ ];
		foreach ( $this->classes as $class => $file ) {
			if (! $this->isExcluded ( $class )) {
				$ret [$class] = \realpath ( $file );
			}
		}
		return $ret;
	}

	/**
	 * Generate a file containing the associative array of classes to preload (classes-files=>[key: class, value: file}).
	 *
	 * @param string $filename
	 * @param ?bool $preserve
	 * @return int
	 */
	public function generateToFile(string $filename, ?bool $preserve = true): int {
		$array = [ ];
		if ($preserve && \file_exists ( $filename )) {
			$array = include $filename;
		}
		$array ['classes-files'] = $this->generateClassesFiles ();
		$content = "<?php\nreturn " . $this->asPhpArray ( $array, 'array', 1, true ) . ";";
		return \file_put_contents ( $filename, $content );
	}

	/**
	 * Adds a directory to be scanned during preloading.
	 *
	 * @param string $dirname
	 * @return \Ubiquity\cache\Preloader
	 */
	public function addDir(string $dirname): Preloader {
		$files = $this->glob_recursive ( $dirname . DIRECTORY_SEPARATOR . '*.php' );
		foreach ( $files as $file ) {
			$class = $this->getClassFullNameFromFile ( $file );
			if (isset ( $class )) {
				$this->addClassFile ( $class, $file );
			}
		}
		return $this;
	}

	/**
	 * Adds a part of an existing library to be preloaded.
	 * The available libraries can be obtained with the getLibraries method.
	 *
	 * @param string $library
	 * @param ?string $part
	 * @return bool
	 */
	public function addLibraryPart(string $library, ?string $part = ''): bool {
		if (isset ( self::$libraries [$library] )) {
			$dir = $this->vendorDir . self::$libraries [$library] . $part;
			if (\file_exists ( $dir )) {
				$this->addDir ( $dir );
				return true;
			}
		}
		return false;
	}

	/**
	 * Adds Ubiquity framework controller and routing classes preload.
	 *
	 * @return \Ubiquity\cache\Preloader
	 */
	public function addUbiquityControllers() {
		$this->addLibraryPart ( 'ubiquity', 'controllers' );
		$this->addClass ( 'Ubiquity\\events\\EventManager' );
		$this->addClass ( 'Ubiquity\\log\\Logger' );
		$this->exclude ( 'Ubiquity\\controllers\\crud', 'Ubiquity\\controllers\\rest', 'Ubiquity\\controllers\\seo', 'Ubiquity\\controllers\\auth' );
		return $this;
	}

	/**
	 * Adds Ubiquity framework cache system classes to preload.
	 *
	 * @return \Ubiquity\cache\Preloader
	 */
	public function addUbiquityCache() {
		$this->addClass ( 'Ubiquity\\cache\\CacheManager' );
		$this->addClass ( 'Ubiquity\\cache\\system\\ArrayCache' );
		return $this;
	}

	/**
	 * Adds Ubiquity framework PDO classes to preload.
	 *
	 * @return \Ubiquity\cache\Preloader
	 */
	public function addUbiquityPdo() {
		$this->addClass ( 'Ubiquity\\db\\Database' );
		$this->addClass ( 'Ubiquity\\cache\\database\\DbCache' );
		$this->addClass ( 'Ubiquity\\db\\SqlUtils' );
		$this->addLibraryPart ( 'ubiquity', 'db/providers' );
		return $this;
	}

	/**
	 * Adds Ubiquity framework ORM classes to preload.
	 *
	 * @return \Ubiquity\cache\Preloader
	 */
	public function addUbiquityORM() {
		$this->addLibraryPart ( 'ubiquity', 'orm' );
		$this->addClass ( 'Ubiquity\\events\\DAOEvents' );
		return $this;
	}

	/**
	 * Adds Ubiquity framework Http classes to preload.
	 *
	 * @return \Ubiquity\cache\Preloader
	 */
	public function addUbiquityHttpUtils() {
		$this->addClass ( 'Ubiquity\\utils\\http\\URequest' );
		$this->addClass ( 'Ubiquity\\utils\\http\\UResponse' );
		$this->addClass ( 'Ubiquity\\utils\\http\\foundation\\PhpHttp' );
		return $this;
	}

	/**
	 * Adds Ubiquity framework MicroTemplateEngine classes to preload.
	 *
	 * @return \Ubiquity\cache\Preloader
	 */
	public function addUbiquityViews() {
		$this->addClass ( 'Ubiquity\\views\\View' );
		$this->addClass ( 'Ubiquity\\views\\engine\\micro\\MicroTemplateEngine' );
		return $this;
	}

	/**
	 * Adds Ubiquity framework Translations classes to preload.
	 *
	 * @return \Ubiquity\cache\Preloader
	 */
	public function addUbiquityTranslations() {
		$this->addLibraryPart ( 'ubiquity', 'translation' );
		return $this;
	}

	/**
	 * Adds Ubiquity-workerman classes to preload.
	 *
	 * @return \Ubiquity\cache\Preloader
	 */
	public function addUbiquityWorkerman() {
		$this->addClasses ( [ 'Ubiquity\\servers\\workerman\\WorkermanServer','Ubiquity\\utils\\http\\foundation\\WorkermanHttp' ] );
		return $this;
	}

	/**
	 * Adds Ubiquity-swoole classes to preload.
	 *
	 * @return \Ubiquity\cache\Preloader
	 */
	public function addUbiquitySwoole() {
		$this->addClasses ( [ 'Ubiquity\\servers\\swoole\\SwooleServer','Ubiquity\\utils\\http\\foundation\\SwooleHttp' ] );
		return $this;
	}

	/**
	 * Adds classes from an application part (app folder) to preload.
	 *
	 * @param string $part
	 * @return \Ubiquity\cache\Preloader
	 */
	public function addApplicationPart(?string $part = '') {
		$this->addLibraryPart ( 'application', $part );
		return $this;
	}

	/**
	 *
	 * @param string $dir
	 * @return \Ubiquity\cache\Preloader
	 */
	public function addApplicationModels($dir = 'models') {
		$this->addLibraryPart ( 'application', $dir );
		return $this;
	}

	/**
	 *
	 * @param string $dir
	 * @return \Ubiquity\cache\Preloader
	 */
	public function addApplicationCache($dir = 'cache') {
		$this->addLibraryPart ( 'application', $dir );
		return $this;
	}

	/**
	 *
	 * @param string $dir
	 * @return \Ubiquity\cache\Preloader
	 */
	public function addApplicationControllers($dir = 'controllers') {
		$this->addLibraryPart ( 'application', $dir );
		$this->exclude ( $dir . '\\MaintenanceController', $dir . '\\Admin' );
		return $this;
	}

	/**
	 * Add Ubiquity hot classes for preloading
	 *
	 * @param boolean $hasDatabase
	 * @return \Ubiquity\cache\Preloader
	 */
	public function addUbiquityBasics($hasDatabase = true) {
		$this->addUbiquityCache ();
		$this->addUbiquityControllers ();
		$this->addUbiquityHttpUtils ();
		if ($hasDatabase) {
			$this->addUbiquityPdo ();
			$this->addUbiquityORM ();
		}
		return $this;
	}

	/**
	 * Adds Twig templating system classes to preload.
	 *
	 * @return \Ubiquity\cache\Preloader
	 */
	public function addUbiquityTwig() {
		$this->addClasses ( [ 'Ubiquity\\views\\engine\\Twig','Twig\\Cache\\FilesystemCache','Twig\\Extension\\CoreExtension','Twig\\Extension\\EscaperExtension','Twig\\Extension\\OptimizerExtension','Twig\\Extension\\StagingExtension','Twig\\ExtensionSet','Twig\\Template','Twig\\TemplateWrapper' ] );
		return $this;
	}

	/**
	 * Defines classes to be preloaded in a file returning an associative array keys : (classes-files, excludeds, paths, classes, libraries-parts, callback).
	 *
	 * @param string $appRoot
	 * @param string $filename
	 * @return bool
	 */
	public static function fromFile(string $appRoot, string $filename): bool {
		if (\file_exists ( $filename )) {
			$array = include $filename;
			return self::fromArray ( $appRoot, $array );
		}
		return false;
	}

	/**
	 * Defines classes to be preloaded with an associative array keys : (classes-files, excludeds, paths, classes, libraries-parts, callback).
	 *
	 * @param string $appRoot
	 * @param array $array
	 * @return bool
	 */
	public static function fromArray(string $appRoot, array $array): bool {
		$pre = new self ( $appRoot );
		self::$count = 0;
		if (isset ( $array ['classes-files'] )) {
			$pre->classes = $array ['classes-files'];
		}
		if (isset ( $array ['excludeds'] )) {
			$pre->excludeds = $array ['excludeds'];
		}
		if (isset ( $array ['paths'] )) {
			foreach ( $array ['paths'] as $path ) {
				$pre->addDir ( $path );
			}
		}
		if (isset ( $array ['classes'] )) {
			foreach ( $array ['classes'] as $class ) {
				$pre->addClass ( $class );
			}
		}
		if (isset ( $array ['libraries-parts'] )) {
			foreach ( $array ['libraries-parts'] as $library => $parts ) {
				foreach ( $parts as $part ) {
					$pre->addLibraryPart ( $library, $part );
				}
			}
		}
		if (isset ( $array ['callback'] )) {
			if (\is_callable ( $array ['callback'] )) {
				$call = $array ['callback'];
				$call ( $pre );
			}
		}
		$pre->load ();
		return self::$count > 0;
	}

	/**
	 * Generates a preload classes-files array from cached files
	 *
	 * @param boolean $resetExisting
	 */
	public function generateClassesFromRunning($resetExisting = true) {
		$cache = \opcache_get_status ( true );
		if ($resetExisting) {
			$this->classes = [ ];
		}
		foreach ( $cache ['scripts'] as $script ) {
			$path = $script ['full_path'];
			$class = $this->getClassFullNameFromFile ( $path );
			if (isset ( $class )) {
				$this->addClassFile ( $class, $path );
			}
		}
	}

	/**
	 * Returns an array of available libraries to be preloaded
	 *
	 * @return array
	 */
	public static function getLibraries() {
		return \array_keys ( self::$libraries );
	}
}


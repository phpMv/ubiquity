<?php
namespace Ubiquity\cache;

/**
 * Ubiquity\cache$Preloader
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
class Preloader {

	private $vendorDir;

	private static $libraries = [
		'application' => './../app/',
		'ubiquity' => 'phpmv/ubiquity/src/Ubiquity/',
		'ubiquity-dev' => 'phpmv/ubiquity-dev/src/Ubiquity/',
		'ubiquity-webtools' => 'phpmv/ubiquity-webtools/src/Ubiquity/',
		'ubiquity-mailer' => 'phpmv/ubiquity-mailer/src/Ubiquity/',
		'ubiquity-swoole' => 'phpmv/ubiquity-swoole/src/Ubiquity/',
		'ubiquity-workerman' => 'phpmv/ubiquity-workerman/src/Ubiquity/',
		'ubiquity-tarantool' => 'phpmv/ubiquity-tarantool/src/Ubiquity/',
		'ubiquity-mysqli' => 'phpmv/ubiquity-mysqli/src/Ubiquity/',
		'phpmv-ui' => 'phpmv/php-mv-ui/Ajax/'
	];

	private $excludeds = [];

	private static $count = 0;

	private $classes = [];

	private $loader;

	/**
	 * Creates a new loader instance for this application.
	 *
	 * @param string $appRoot
	 *        	The app root
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
		foreach ($paths as $path) {
			$this->addDir($path);
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
		$this->excludeds = \array_merge($this->excludeds, $names);
		return $this;
	}

	/**
	 * Adds a class to preload.
	 *
	 * @param string $class
	 * @return bool
	 */
	public function addClass(string $class): bool {
		if (! $this->isExcluded($class)) {
			if (! isset($this->classes[$class])) {
				$path = $this->getPathFromClass($class);
				if (isset($path)) {
					$this->classes[$class] = $path;
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
		foreach ($classes as $class) {
			$this->addClass($class);
		}
	}

	/**
	 * Preload all added classes.
	 */
	public function load() {
		foreach ($this->classes as $class => $file) {
			if (! $this->isExcluded($class)) {
				$this->loadClass($class, $file);
			}
		}
	}

	/**
	 * Returns a generated associative array of classes to preload (key: class, value: file).
	 *
	 * @return array
	 */
	public function generateClassesFiles(): array {
		$ret = [];
		foreach ($this->classes as $class => $file) {
			if (! $this->isExcluded($class)) {
				$ret[$class] = \realpath($file);
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
		$array = [];
		if ($preserve && \file_exists($filename)) {
			$array = include $filename;
		}
		$array['classes-files'] = $this->generateClassesFiles();
		$content = "<?php\nreturn " . \var_export($array, true) . ";";
		return \file_put_contents($filename, $content);
	}

	/**
	 * Adds a directory to be scanned during preloading.
	 *
	 * @param string $dirname
	 * @return \Ubiquity\cache\Preloader
	 */
	public function addDir(string $dirname): Preloader {
		$files = $this->glob_recursive($dirname . DIRECTORY_SEPARATOR . '*.php');
		foreach ($files as $file) {
			$class = $this->getClassFullNameFromFile($file);
			if (isset($class)) {
				$this->addClassFile($class, $file);
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
		if (isset(self::$libraries[$library])) {
			$dir = $this->vendorDir . self::$libraries[$library] . $part;
			if (\file_exists($dir)) {
				$this->addDir($dir);
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
		$this->addLibraryPart('ubiquity', 'controllers');
		return $this;
	}

	/**
	 * Adds Ubiquity framework cache system classes to preload.
	 *
	 * @return \Ubiquity\cache\Preloader
	 */
	public function addUbiquityCache() {
		$this->addLibraryPart('ubiquity', 'cache');
		return $this;
	}

	/**
	 * Adds Ubiquity framework PDO classes to preload.
	 *
	 * @return \Ubiquity\cache\Preloader
	 */
	public function addUbiquityPdo() {
		$this->addClass('Ubiquity\\db\\Database');
		$this->addLibraryPart('ubiquity', 'db/providers');
		return $this;
	}

	/**
	 * Adds Ubiquity framework ORM classes to preload.
	 *
	 * @return \Ubiquity\cache\Preloader
	 */
	public function addUbiquityORM() {
		$this->addLibraryPart('ubiquity', 'orm');
		return $this;
	}

	/**
	 * Adds Ubiquity framework Http classes to preload.
	 *
	 * @return \Ubiquity\cache\Preloader
	 */
	public function addUbiquityHttpUtils() {
		$this->addLibraryPart('ubiquity', 'utils/http');
		return $this;
	}

	/**
	 * Adds Ubiquity framework MicroTemplateEngine classes to preload.
	 *
	 * @return \Ubiquity\cache\Preloader
	 */
	public function addUbiquityViews() {
		$this->addClass('Ubiquity\\views\\engine\\micro\\MicroTemplateEngine');
		return $this;
	}

	/**
	 * Adds Ubiquity framework Translations classes to preload.
	 *
	 * @return \Ubiquity\cache\Preloader
	 */
	public function addUbiquityTranslations() {
		$this->addLibraryPart('ubiquity', 'translation');
		return $this;
	}

	/**
	 * Adds Ubiquity-workerman classes to preload.
	 *
	 * @return \Ubiquity\cache\Preloader
	 */
	public function addUbiquityWorkerman() {
		$this->addLibraryPart('ubiquity-workerman');
		return $this;
	}

	/**
	 * Adds classes from an application part (app folder) to preload.
	 *
	 * @param string $part
	 * @return boolean
	 */
	public function addApplicationPart(?string $part = '') {
		return $this->addLibraryPart('application', $part);
	}

	/**
	 *
	 * @param string $dir
	 * @return boolean
	 */
	public function addApplicationModels($dir = 'models') {
		return $this->addLibraryPart('application', $dir);
	}

	/**
	 *
	 * @param string $dir
	 * @return boolean
	 */
	public function addApplicationControllers($dir = 'controllers') {
		return $this->addLibraryPart('application', $dir);
	}

	/**
	 * Adds Twig templating system classes to preload.
	 *
	 * @return \Ubiquity\cache\Preloader
	 */
	public function addUbiquityTwig() {
		$this->addClasses([
			'Ubiquity\\views\\engine\\Twig',
			'Twig\Cache\FilesystemCache',
			'Twig\Extension\CoreExtension',
			'Twig\Extension\EscaperExtension',
			'Twig\Extension\OptimizerExtension',
			'Twig\Extension\StagingExtension',
			'Twig\ExtensionSet',
			'Twig\Template',
			'Twig\TemplateWrapper'
		]);
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
		if (\file_exists($filename)) {
			$array = include $filename;
			return self::fromArray($appRoot, $array);
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
		$pre = new self($appRoot);
		self::$count = 0;
		if (isset($array['classes-files'])) {
			$pre->classes = $array['classes-files'];
		}
		if (isset($array['excludeds'])) {
			$pre->excludeds = $array['excludeds'];
		}
		if (isset($array['paths'])) {
			foreach ($array['paths'] as $path) {
				$pre->addDir($path);
			}
		}
		if (isset($array['classes'])) {
			foreach ($array['classes'] as $class) {
				$pre->addClass($class);
			}
		}
		if (isset($array['libraries-parts'])) {
			foreach ($array['libraries-parts'] as $library => $parts) {
				foreach ($parts as $part) {
					$pre->addLibraryPart($library, $part);
				}
			}
		}
		if (isset($array['callback'])) {
			if (\is_callable($array['callback'])) {
				$call = $array['callback'];
				$call($pre);
			}
		}
		$pre->load();
		return self::$count > 0;
	}

	/**
	 * Returns an array of available libraries to be preloaded
	 *
	 * @return array
	 */
	public static function getLibraries() {
		return \array_keys(self::$libraries);
	}

	private function addClassFile($class, $file) {
		if (! isset($this->classes[$class])) {
			$this->classes[$class] = $file;
		}
	}

	private function loadClass($class, $file = null) {
		if (! \class_exists($class, false)) {
			$file = $file ?? $this->getPathFromClass($class);
			if (isset($file)) {
				$this->loadFile($file);
			}
		}
		if (\class_exists($class, false)) {
			echo "$class loaded !<br>";
		}
	}

	private function getPathFromClass(string $class): ?string {
		$classPath = $this->loader->findFile($class);
		if (false !== $classPath) {
			return \realpath($classPath);
		}
		return null;
	}

	private function loadFile(string $file): void {
		require_once $file;
		self::$count ++;
	}

	private function isExcluded(string $name): bool {
		foreach ($this->excludeds as $excluded) {
			if (\strpos($name, $excluded) === 0) {
				return true;
			}
		}
		return false;
	}

	private function glob_recursive($pattern, $flags = 0) {
		$files = \glob($pattern, $flags);
		foreach (\glob(\dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
			$files = \array_merge($files, $this->glob_recursive($dir . '/' . \basename($pattern), $flags));
		}
		return $files;
	}

	private function getClassFullNameFromFile($filePathName, $backSlash = false) {
		$phpCode = \file_get_contents($filePathName);
		$ns = $this->getClassNamespaceFromPhpCode($phpCode);
		if ($backSlash && $ns != null) {
			$ns = "\\" . $ns;
		}
		return $ns . '\\' . $this->getClassNameFromPhpCode($phpCode);
	}

	private function getClassNamespaceFromPhpCode($phpCode) {
		$tokens = \token_get_all($phpCode);
		$count = \count($tokens);
		$i = 0;
		$namespace = '';
		$namespace_ok = false;
		while ($i < $count) {
			$token = $tokens[$i];
			if (\is_array($token) && $token[0] === T_NAMESPACE) {
				// Found namespace declaration
				while (++ $i < $count) {
					if ($tokens[$i] === ';') {
						$namespace_ok = true;
						$namespace = \trim($namespace);
						break;
					}
					$namespace .= \is_array($tokens[$i]) ? $tokens[$i][1] : $tokens[$i];
				}
				break;
			}
			$i ++;
		}
		if (! $namespace_ok) {
			return null;
		}
		return $namespace;
	}

	private function getClassNameFromPhpCode($phpCode) {
		$classes = array();
		$tokens = \token_get_all($phpCode);
		$count = count($tokens);
		for ($i = 2; $i < $count; $i ++) {
			if ($tokens[$i - 2][0] == T_CLASS && $tokens[$i - 1][0] == T_WHITESPACE && $tokens[$i][0] == T_STRING) {
				$class_name = $tokens[$i][1];
				$classes[] = $class_name;
			}
		}
		if (isset($classes[0]))
			return $classes[0];
		return null;
	}
}


<?php

namespace Ubiquity\views\engine\latte;

use Latte\Engine;
use Latte\Loader;
use Latte\Loaders\FileLoader;
use Ubiquity\cache\CacheManager;
use Ubiquity\controllers\Startup;
use Ubiquity\core\Framework;
use Ubiquity\events\EventsManager;
use Ubiquity\events\ViewEvents;
use Ubiquity\themes\ThemesManager;
use Ubiquity\utils\base\UFileSystem;
use Ubiquity\views\engine\TemplateEngine;
use Ubiquity\views\engine\TemplateGenerator;

class Latte extends TemplateEngine {
	private Engine $engine;
	private ULatteFileLoader $loader;

	public function __construct($options = []) {
		$this->engine = new Engine();
		$cacheDir = CacheManager::getAbsoluteCacheDirectory() . \DS . 'views';
		$this->engine->setTempDirectory($cacheDir);
		$this->loader = new ULatteFileLoader(\ROOT . \DS . 'views' . \DS);
		$this->loader->addPath(Startup::getFrameworkDir() . \DS . '..' . \DS . 'core' . \DS . 'views', 'framework');
		$this->engine->setLoader($this->loader);

		if (isset ($options ['activeTheme'])) {
			ThemesManager::setActiveThemeFromTwig($options ['activeTheme']);
			$this->setTheme($options ['activeTheme'], ThemesManager::THEMES_FOLDER);
			unset ($options ['activeTheme']);
		} else {
			$this->loader->addPath(\ROOT . \DS . 'views', 'activeTheme');
		}
		if (($options ['cache'] ?? false) === true) {
			$this->engine->setAutoRefresh(false);
		}
		$this->addFunctions();
	}

	/**
	 * @inheritDoc
	 */
	public function render(string $fileName, ?array $pData = [], bool $asString = false) {
		$pData ['config'] = Startup::getConfig();
		$pData['app'] = new Framework();
		EventsManager::trigger(ViewEvents::BEFORE_RENDER, $viewName, $pData);
		if ($asString === true) {
			return $this->engine->renderToString($fileName, $pData);
		}
		$this->engine->render($fileName, $pData);
		EventsManager::trigger(ViewEvents::AFTER_RENDER, $render, $viewName, $pData);
	}

	/**
	 * @inheritDoc
	 */
	public function getBlockNames(string $templateName): array {
		$tpl = $this->engine->createTemplate($templateName);
		return $tpl->getBlockNames();
	}

	/**
	 * @inheritDoc
	 */
	public function getCode(string $templateName): string {
		return UFileSystem::load($this->loader->getUniqueId($templateName));
	}

	public function addPath(string $path, string $namespace): void {
		$this->loader->addPath($path, $namespace);
	}

	/**
	 * @inheritDoc
	 */
	public function setTheme(string $theme, string $themeFolder = ThemesManager::THEMES_FOLDER): string {
		$path = parent::setTheme($theme, $themeFolder);
		$this->loader->addPath($path, 'activeTheme');
		return $path;
	}

	/**
	 * @inheritDoc
	 */
	public function addFunction(string $name, $callback, array $options = []): void {
		$this->engine->addFunction($name, $callback);
	}

	/**
	 * @inheritDoc
	 */
	protected function addFilter(string $name, $callback, array $options = []): void {
		$this->engine->addFilter($name, $callback);
	}

	/**
	 * @inheritDoc
	 */
	protected function addExtension($extension): void {
		$this->engine->addExtension($extension);
	}

	protected function safeString(string $str) {
		return new \Latte\Runtime\Html($str);
	}

	public function getGenerator(): ?TemplateGenerator {
		return new LatteTemplateGenerator();
	}

	public function getFrameworkTemplateFolder(): string {
		return 'latte/';
	}

}

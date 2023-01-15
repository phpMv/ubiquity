<?php

namespace Ubiquity\views\engine;

use Ubiquity\assets\AssetsManager;
use Ubiquity\controllers\Router;
use Ubiquity\domains\DDDManager;
use Ubiquity\exceptions\ThemesException;
use Ubiquity\themes\ThemesManager;

/**
 * Abstract template engine.
 * Ubiquity\views\engine$TemplateEngine
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.1.0
 *
 */
abstract class TemplateEngine {

	/**
	 * Renders a view.
	 *
	 * @param string $fileName
	 * @param array|null $pData
	 * @param boolean $asString
	 */
	abstract public function render(string $fileName, ?array $pData = [], bool $asString = false);

	/**
	 * Returns the defined block names.
	 *
	 * @param string $templateName
	 */
	abstract public function getBlockNames(string $templateName): array;

	/**
	 * Returns the source code of the template
	 *
	 * @param string $templateName
	 */
	abstract public function getCode(string $templateName): string;

	abstract public function addFunction(string $name, $callback, array $options = []): void;

	abstract protected function addFilter(string $name, $callback, array $options = []): void;

	abstract protected function addExtension($extension): void;

	abstract public function getFrameworkTemplateFolder(): string;

	protected function hasThemeResource(&$resource): bool {
		$resource = \str_replace('@activeTheme/', '', $resource, $count);
		return $count > 0;
	}

	/**
	 * Defines the activeTheme.
	 * **activeTheme** namespace is @activeTheme
	 *
	 * @param string $theme
	 * @param string $themeFolder
	 * @throws ThemesException
	 */
	public function setTheme(string $theme, string $themeFolder = ThemesManager::THEMES_FOLDER): string {
		$root = DDDManager::getActiveViewFolder();
		$path = $root . $themeFolder . \DS . $theme;
		if ($theme == '') {
			$path = $root;
		}
		if (!\file_exists($path)) {
			throw new ThemesException (sprintf('The path `%s` does not exists!', $path));
		}
		return $path;
	}

	protected function addFunctions(): void {
		$safe = ['is_safe' => ['html']];
		$this->addFunction('path', function ($name, $params = [], $absolute = false) {
			return Router::path($name, $params, $absolute);
		});

		$this->addFunction('url', function ($name, $params = []) {
			return Router::url($name, $params);
		});

		if (\class_exists('\\Ubiquity\\security\\csrf\\UCsrfHttp')) {
			$this->addFunction('csrfMeta', function ($name) {
				return \Ubiquity\security\csrf\UCsrfHttp::getTokenMeta($name);
			}, $safe);
			$this->addFunction('csrf', function ($name) {
				return \Ubiquity\security\csrf\UCsrfHttp::getTokenField($name);
			}, $safe);
		}

		if (\class_exists('\\Ubiquity\security\\acl\\AclManager')) {
			$this->addFunction('isAllowedRoute', function ($role, $routeName) {
				return \Ubiquity\security\acl\AclManager::isAllowedRoute($role, $routeName);
			}, []);
		}

		$this->addFunction('css', function ($resource, $parameters = [], $absolute = false) {
			if ($this->hasThemeResource($resource)) {
				return $this->safeString(AssetsManager::css_($resource, $parameters, $absolute));
			}
			return $this->safeString(AssetsManager::css($resource, $parameters, $absolute));
		}, $safe);

		$this->addFunction('js', function ($resource, $parameters = [], $absolute = false) {
			if ($this->hasThemeResource($resource)) {
				return $this->safeString(AssetsManager::js_($resource, $parameters, $absolute));
			}
			return $this->safeString(AssetsManager::js($resource, $parameters, $absolute));
		}, $safe);

		$this->addFunction('img', function ($resource, $parameters = [], $absolute = false) {
			if ($this->hasThemeResource($resource)) {
				return $this->safeString(AssetsManager::img_($resource, $parameters, $absolute));
			}
			return $this->safeString(AssetsManager::img($resource, $parameters, $absolute));
		}, $safe);
	}

	protected function safeString(string $str): string {
		return $str;
	}

	abstract public function getGenerator(): ?TemplateGenerator;

	/**
	 * Generates the source for this engine from a twig model template.
	 * @param string $templateName
	 * @return string
	 */
	public function generateTemplateSourceFromFile(string $templateName): string {
		$result = $this->getCode($templateName);
		return $this->generateTemplateSource($result);
	}

	public function generateTemplateSource(string $source): string {
		$gen = $this->getGenerator();
		if ($gen != null) {
			return $gen->parseFromTwig($source);
		}
		return $source;
	}
}

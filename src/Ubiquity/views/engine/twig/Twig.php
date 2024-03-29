<?php

namespace Ubiquity\views\engine\twig;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;
use Ubiquity\cache\CacheManager;
use Ubiquity\controllers\Startup;
use Ubiquity\events\EventsManager;
use Ubiquity\events\ViewEvents;
use Ubiquity\exceptions\ThemesException;
use Ubiquity\themes\ThemesManager;
use Ubiquity\translation\TranslatorManager;
use Ubiquity\utils\base\UFileSystem;
use Ubiquity\views\engine\TemplateEngine;
use Ubiquity\views\engine\TemplateGenerator;

/**
 * Ubiquity Twig template engine.
 *
 * Ubiquity\views\engine$Twig
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.13
 *
 */
class Twig extends TemplateEngine {
	private $twig;
	private $loader;

	public function __construct($options = []) {
		$loader = new FilesystemLoader (\ROOT . \DS . 'views' . \DS);
		$loader->addPath(Startup::getFrameworkDir() . '/../core/views/engines/twig', 'framework');
		$this->loader = $loader;

		if (($options ['cache'] ?? false) === true) {
			$options ['cache'] = CacheManager::getCacheSubDirectory('views');
		}

		$this->twig = new Environment ($loader, $options);

		if (isset($options['extensions'])) {
			foreach ($options['extensions'] as $ext) {
				$this->twig->addExtension(new $ext());
			}
		}

		if (isset ($options ['activeTheme'])) {
			ThemesManager::setActiveThemeFromTwig($options ['activeTheme']);
			$this->setTheme($options ['activeTheme'], ThemesManager::THEMES_FOLDER);
			unset ($options ['activeTheme']);
		} else {
			$this->loader->setPaths([\ROOT . \DS . 'views'], 'activeTheme');
		}

		$this->addFunctions();
	}

	protected function addFunctions(): void {
		parent::addFunctions();
		$t = new TwigFunction ('t', function ($context, $id, array $parameters = array(), $domain = null, $locale = null) {
			$trans = TranslatorManager::trans($id, $parameters, $domain, $locale);
			return $this->twig->createTemplate($trans)->render($context);
		}, ['needs_context' => true]);

		$tc = new TwigFunction ('tc', function ($context, $id, array $choice, array $parameters = array(), $domain = null, $locale = null) {
			$trans = TranslatorManager::transChoice($id, $choice, $parameters, $domain, $locale);
			return $this->twig->createTemplate($trans)->render($context);
		}, ['needs_context' => true]);
		$this->twig->addFunction($t);
		$this->twig->addFunction($tc);

		$test = new TwigTest ('instanceOf', function ($var, $class) {
			return $var instanceof $class;
		});
		$this->twig->addTest($test);
		$this->twig->addGlobal('app', $this->fw);
	}

	public function addFunction(string $name, $callback, array $options = []): void {
		$this->twig->addFunction(new TwigFunction ($name, $callback, $options));
	}

	protected function addFilter(string $name, $callback, array $options = []): void {
		$this->twig->addFilter(new TwigFilter($name, $callback, $options));
	}

	protected function addExtension($extension): void {
		$this->twig->addExtension($extension);
	}

	/*
	 * (non-PHPdoc)
	 * @see TemplateEngine::render()
	 */
	public function render(string $viewName, ?array $pData = [], bool $asString = false) {
		$pData ['config'] = Startup::getConfig();
		EventsManager::trigger(ViewEvents::BEFORE_RENDER, $viewName, $pData);
		$render = $this->twig->render($viewName, $pData);
		EventsManager::trigger(ViewEvents::AFTER_RENDER, $render, $viewName, $pData);
		if ($asString) {
			return $render;
		} else {
			echo $render;
		}
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\views\engine\TemplateEngine::getBlockNames()
	 */
	public function getBlockNames(string $templateName): array {
		try {
			$result = $this->twig->load($templateName)->getBlockNames();
		} catch (\Error $e) {
			$result = [];
		}
		return $result;
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\views\engine\TemplateEngine::getCode()
	 */
	public function getCode(string $templateName): string {
		return UFileSystem::load($this->twig->load($templateName)->getSourceContext()->getPath());
	}

	/**
	 * Adds a new path in a namespace
	 *
	 * @param string $path The path to add
	 * @param string $namespace The namespace to use
	 */
	public function addPath(string $path, string $namespace) {
		$this->loader->addPath($path, $namespace);
	}

	/**
	 * Sets a path in a namespace
	 *
	 * @param array $paths The paths to add
	 * @param string $namespace The namespace to use
	 */
	public function setPaths(array $paths, string $namespace) {
		$this->loader->setPaths($paths, $namespace);
	}

	/**
	 * @param string $theme
	 * @param string $themeFolder
	 * @return string|void
	 * @throws ThemesException
	 */
	public function setTheme(string $theme, string $themeFolder = ThemesManager::THEMES_FOLDER): string {
		$path = parent::setTheme($theme, $themeFolder);
		$this->loader->setPaths([$path], 'activeTheme');
		return $path;
	}

	/**
	 * Checks if we have the source code of a template, given its name.
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function exists(string $name): bool {
		return $this->twig->getLoader()->exists($name);
	}

	public function getGenerator(): ?TemplateGenerator {
		return null;
	}

}

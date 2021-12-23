<?php

namespace Ubiquity\views\engine;

use Twig\Environment;
use Twig\TwigFunction;
use Twig\TwigTest;
use Twig\Loader\FilesystemLoader;
use Ubiquity\cache\CacheManager;
use Ubiquity\controllers\Router;
use Ubiquity\controllers\Startup;
use Ubiquity\core\Framework;
use Ubiquity\events\EventsManager;
use Ubiquity\events\ViewEvents;
use Ubiquity\exceptions\ThemesException;
use Ubiquity\translation\TranslatorManager;
use Ubiquity\utils\base\UFileSystem;
use Ubiquity\themes\ThemesManager;
use Ubiquity\assets\AssetsManager;
use Ubiquity\domains\DDDManager;

/**
 * Ubiquity Twig template engine.
 *
 * Ubiquity\views\engine$Twig
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.12
 *
 */
class Twig extends TemplateEngine {
	private $twig;
	private $loader;
	
	public function __construct($options = []) {
		$loader = new FilesystemLoader (\ROOT . \DS . 'views' . \DS);
		$loader->addPath(Startup::getFrameworkDir().\DS .'..'.\DS .'core'.\DS.'views', 'framework');
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
		
		$this->addHelpers();
	}
	
	protected function addHelpers() {
		$this->addFunction('path', function ($name, $params = [], $absolute = false) {
			return Router::path($name, $params, $absolute);
		});
			
		$this->addFunction('url', function ($name, $params = []) {
			return Router::url($name, $params);
		});
				
		if (\class_exists('\\Ubiquity\\security\\csrf\\UCsrfHttp')) {
			$this->addFunction('csrfMeta', function ($name) {
				return \Ubiquity\security\csrf\UCsrfHttp::getTokenMeta($name);
			}, true);
				$this->addFunction('csrf', function ($name) {
					return \Ubiquity\security\csrf\UCsrfHttp::getTokenField($name);
				}, true);
		}
		
		if (\class_exists('\\Ubiquity\security\\acl\\AclManager')) {
			$this->addFunction('isAllowedRoute', function ($role, $routeName) {
				return \Ubiquity\security\acl\AclManager::isAllowedRoute($role, $routeName);
			}, true);
		}
		
		$this->addFunction('css', function ($resource, $parameters = [], $absolute = false) {
			if ($this->hasThemeResource($resource)) {
				return AssetsManager::css_($resource, $parameters, $absolute);
			}
			return AssetsManager::css($resource, $parameters, $absolute);
		}, true);
			
		$this->addFunction('js', function ($resource, $parameters = [], $absolute = false) {
			if ($this->hasThemeResource($resource)) {
				return AssetsManager::js_($resource, $parameters, $absolute);
			}
			return AssetsManager::js($resource, $parameters, $absolute);
		}, true);
		
		$this->addFunction('img', function ($resource, $parameters = [], $absolute = false) {
			if ($this->hasThemeResource($resource)) {
				return AssetsManager::img_($resource, $parameters, $absolute);
			}
			return AssetsManager::img($resource, $parameters, $absolute);
		}, true);
			
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
		$this->twig->addGlobal('app', new Framework ());
	}
	
	protected function hasThemeResource(&$resource) {
		$resource = \str_replace('@activeTheme/', '', $resource, $count);
		return $count > 0;
	}
	
	protected function addFunction($name, $callback, $safe = false) {
		$options = ($safe) ? ['is_safe' => ['html']] : [];
		$this->twig->addFunction(new TwigFunction ($name, $callback, $options));
	}
	
	/*
	 * (non-PHPdoc)
	 * @see TemplateEngine::render()
	 */
	public function render($viewName, $pData, $asString) {
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
	public function getBlockNames($templateName) {
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
	public function getCode($templateName) {
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
	 * Defines the activeTheme.
	 * **activeTheme** namespace is @activeTheme
	 *
	 * @param string $theme
	 * @param string $themeFolder
	 * @throws ThemesException
	 */
	public function setTheme($theme, $themeFolder = ThemesManager::THEMES_FOLDER) {
		$root=DDDManager::getActiveViewFolder();
		$path = $root . $themeFolder . \DS . $theme;
		if ($theme == '') {
			$path = $root;
		}
		if (\file_exists($path)) {
			$this->loader->setPaths([$path], 'activeTheme');
		} else {
			throw new ThemesException (sprintf('The path `%s` does not exists!', $path));
		}
	}
	
	/**
	 * Checks if we have the source code of a template, given its name.
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function exists($name) {
		return $this->twig->getLoader()->exists($name);
	}
}

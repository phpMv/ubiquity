<?php

namespace Ubiquity\views\engine;

use Ubiquity\controllers\Startup;
use Ubiquity\controllers\Router;
use Ubiquity\cache\CacheManager;
use Ubiquity\core\Framework;
use Ubiquity\utils\base\UFileSystem;
use Ubiquity\translation\TranslatorManager;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Twig\TwigFunction;
use Twig\TwigTest;

/**
 * Ubiquity Twig template engine.
 *
 * Ubiquity\views\engine$Twig
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.5
 *
 */
class Twig extends TemplateEngine {
	private $twig;
	private $loader;

	public function __construct($options = array()) {
		$loader = new FilesystemLoader ( \ROOT . \DS . "views" . \DS );
		$loader->addPath ( implode ( \DS, [ Startup::getFrameworkDir (),"..","core","views" ] ) . \DS, "framework" );
		if (isset ( $options ["cache"] ) && $options ["cache"] === true)
			$options ["cache"] = CacheManager::getCacheSubDirectory ( "views" );
		$this->twig = new Environment ( $loader, $options );

		$function = new TwigFunction ( 'path', function ($name, $params = [], $absolute = false) {
			return Router::path ( $name, $params, $absolute );
		} );
		$this->twig->addFunction ( $function );
		$function = new TwigFunction ( 'url', function ($name, $params) {
			return Router::url ( $name, $params );
		} );
		$this->twig->addFunction ( $function );

		$function = new TwigFunction ( 't', function ($context, $id, array $parameters = array(), $domain = null, $locale = null) {
			$trans = TranslatorManager::trans ( $id, $parameters, $domain, $locale );
			return $this->twig->createTemplate ( $trans )->render ( $context );
		}, [ 'needs_context' => true ] );
		$this->twig->addFunction ( $function );

		$test = new TwigTest ( 'instanceOf', function ($var, $class) {
			return $var instanceof $class;
		} );
		$this->twig->addTest ( $test );
		$this->twig->addGlobal ( "app", new Framework () );
		$this->loader = $loader;
	}

	/*
	 * (non-PHPdoc)
	 * @see TemplateEngine::render()
	 */
	public function render($viewName, $pData, $asString) {
		$pData ["config"] = Startup::getConfig ();
		$render = $this->twig->render ( $viewName, $pData );
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
		return $this->twig->load ( $templateName )->getBlockNames ();
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\views\engine\TemplateEngine::getCode()
	 */
	public function getCode($templateName) {
		return UFileSystem::load ( $this->twig->load ( $templateName )->getSourceContext ()->getPath () );
	}

	/**
	 * Adds a new path in a namespace
	 *
	 * @param string $path
	 *        	The path to add
	 * @param string $namespace
	 *        	The namespace to use
	 */
	public function addPath(string $path, string $namespace) {
		$this->loader->addPath ( $path, $namespace );
	}
}

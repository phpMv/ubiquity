<?php

namespace Ubiquity\themes;

use Ubiquity\controllers\Startup;
use Ubiquity\views\engine\Twig;
use Ubiquity\exceptions\ThemesException;
use Ubiquity\events\EventsManager;
use Ubiquity\events\ViewEvents;
use Ubiquity\utils\base\UFileSystem;

/**
 * Themes manager.
 * Ubiquity\themes$ThemesManager
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 * @since Ubiquity 2.1.0
 *
 */
class ThemesManager {
	const THEMES_FOLDER = 'themes';
	private static $activeTheme;

	public static function start(string $activeTheme = null) {
		self::setActiveTheme ( $activeTheme ?? 'default');
	}

	public static function getActiveTheme() {
		return self::$activeTheme;
	}

	/**
	 * Sets the activeTheme
	 *
	 * @param string $activeTheme
	 * @throws ThemesException
	 */
	public static function setActiveTheme($activeTheme) {
		self::$activeTheme = $activeTheme ?? 'default';
		$engineInstance = Startup::getTempateEngineInstance ();
		if ($engineInstance instanceof Twig) {
			$engineInstance->setTheme ( $activeTheme, self::THEMES_FOLDER );
		} else {
			throw new ThemesException ( 'Template engine must be an instance of Twig for themes activation!' );
		}
	}

	/**
	 * Returns the names of available themes.
	 *
	 * @return string[]
	 */
	public static function getAvailableThemes() {
		$path = \ROOT . \DS . 'views' . \DS . self::THEMES_FOLDER . \DS . '*';
		$dirs = \glob ( $path, GLOB_ONLYDIR | GLOB_NOSORT );
		$result = [ ];
		foreach ( $dirs as $dir ) {
			$result [] = basename ( $dir );
		}
		return $result;
	}

	/**
	 * Adds a listener before theme rendering.
	 * The callback function takes the following parameters: $view (the view name), $pData (array of datas sent to the view)
	 *
	 * @param callable $callback
	 */
	public static function onBeforeRender($callback) {
		EventsManager::addListener ( ViewEvents::BEFORE_RENDER, $callback );
	}

	/**
	 * Adds a listener after theme rendering.
	 * The callback function takes the following parameters: $render (the response string), $view (the view name), $pData (array of datas sent to the view)
	 *
	 * @param callable $callback
	 */
	public static function onAfterRender($callback) {
		EventsManager::addListener ( ViewEvents::AFTER_RENDER, $callback );
	}
}


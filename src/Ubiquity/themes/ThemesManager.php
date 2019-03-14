<?php

namespace Ubiquity\themes;

use Ubiquity\controllers\Startup;
use Ubiquity\views\engine\Twig;
use Ubiquity\exceptions\ThemesException;

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

	public static function setActiveTheme($activeTheme) {
		self::$activeTheme = $activeTheme ?? 'default';
		$engineInstance = Startup::getTempateEngineInstance ();
		if ($engineInstance instanceof Twig) {
			$engineInstance->setTheme ( $activeTheme, self::THEMES_FOLDER );
		} else {
			throw new ThemesException ( 'Template engine must be an instance of Twig for themes activation!' );
		}
	}
}


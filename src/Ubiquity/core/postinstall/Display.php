<?php

namespace Ubiquity\core\postinstall;

use Ubiquity\core\Framework;
use Ubiquity\themes\ThemesManager;

/**
 * Ubiquity\core\postinstall$Display
 * This class is part of Ubiquity
 *
 * @author jc
 * @version 1.0.0
 *
 */
class Display {
	private static $links = [ "Website" => "https://ubiquity.kobject.net","Guide" => "https://micro-framework.readthedocs.io/en/latest/?badge=latest","Documentation API" => "https://api.kobject.net/ubiquity/","GitHub" => "https://github.com/phpMv/ubiquity" ];

	public static function semanticMenu($id, $semantic) {
		$links = self::getLinks ();
		$menu = $semantic->htmlMenu ( $id, \array_keys ( $links ) );
		$menu->asLinks ( \array_values ( $links ), 'new' );
		$menu->setSecondary ();
		return $menu;
	}

	public static function getLinks() {
		$links = self::$links;
		if (Framework::hasAdmin ()) {
			$links ['Webtools'] = 'Admin';
		}
		return $links;
	}

	public static function getPageInfos() {
		return [ 'Controller' => Framework::getController (),'Action' => Framework::getAction (),'Route' => Framework::getUrl (),'Path' => '/','ActiveTheme' => ThemesManager::getActiveTheme () ?? 'none','Cache'=>Framework::getCacheSystem(),'Annotations'=>Framework::getAnnotationsEngine()];
	}

	public static function getDefaultPage() {
		$activeTheme = ThemesManager::getActiveTheme ();
		if ($activeTheme == null || ThemesManager::isCustom ( $activeTheme )) {
			$activeTheme = "index";
		}
		return '@framework/index/' . $activeTheme . '.html';
	}

	public static function getThemes() {
		return ThemesManager::getAvailableThemes ();
	}
}


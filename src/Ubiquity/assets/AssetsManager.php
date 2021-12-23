<?php

/**
 * Assets managment
 */
namespace Ubiquity\assets;

use Ubiquity\themes\ThemesManager;
use Ubiquity\utils\base\UArray;

/**
 * Assets manager for css and js inclusions in templates.
 * Ubiquity\assets$AssetsManager
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.3
 * @since Ubiquity 2.1.0
 *
 */
class AssetsManager {
	private static $assetsFolder = '/assets/';
	private static $siteURL;

	private static function gString($template, $variable, $attributes = []) {
		$implode = UArray::implodeAsso ( $attributes, ' ','=','' );
		return \sprintf ( $template, $variable, $implode );
	}

	private static function script($src, $attributes = []) {
		return self::gString ( '<script src="%s" %s></script>', $src, $attributes );
	}

	private static function stylesheet($link, $attributes = []) {
		$attributes['type']??='text/css';
		return self::gString ( '<link href="%s" rel="stylesheet" %s>', $link, $attributes );
	}

	private static function image($src, $attributes = []) {
		$attributes['alt']??='Alternate text required';
		return self::gString ( '<img src="%s" %s>', $src, $attributes );
	}

	/**
	 * Starts the assets manager.
	 * Essential to define the siteURL part
	 *
	 * @param array $config
	 */
	public static function start(&$config) {
		$siteURL = $config ['siteUrl'] ?? '';
		self::$siteURL = \rtrim ( $siteURL, '/' );
	}

	public static function setAssetsFolder($assetsFolder='/assets/'){
		self::$assetsFolder=$assetsFolder;
	}

	/**
	 * Returns the absolute or relative url to the resource.
	 *
	 * @param string $resource
	 * @param boolean $absolute
	 * @return string
	 */
	public static function getUrl($resource, $absolute = false) {
		if (\strpos ( $resource, '//' ) !== false) {
			return $resource;
		}
		if ($absolute) {
			return self::$siteURL . self::$assetsFolder . $resource;
		}
		return \ltrim ( self::$assetsFolder, '/' ) . $resource;
	}

	/**
	 * Returns the absolute or relative url for a resource in the **activeTheme**.
	 *
	 * @param string $resource
	 * @param boolean $absolute
	 * @return string
	 */
	public static function getActiveThemeUrl($resource, $absolute = false) {
		$activeTheme = ThemesManager::getActiveTheme ();
		return self::getThemeUrl ( $activeTheme, $resource, $absolute );
	}

	/**
	 * Returns the absolute or relative url for a resource in a theme.
	 *
	 * @param string $theme
	 * @param string $resource
	 * @param boolean $absolute
	 * @return string
	 */
	public static function getThemeUrl($theme, $resource, $absolute = false) {
		if ($absolute) {
			return self::$siteURL . self::$assetsFolder . $theme . '/' . $resource;
		}
		return \ltrim ( self::$assetsFolder, '/' ) . $theme . '/' . $resource;
	}

	/**
	 * Returns the script inclusion for a javascript resource.
	 *
	 * @param string $resource The javascript resource to include
	 * @param array $attributes The other html attributes of the script element
	 * @param boolean $absolute True if url must be absolute (containing siteUrl)
	 * @return string
	 */
	public static function js($resource, $attributes = [], $absolute = false) {
		return self::script ( self::getUrl ( $resource, $absolute ), $attributes );
	}

	/**
	 * Returns the css inclusion for a stylesheet resource.
	 *
	 * @param string $resource The css resource to include
	 * @param array $attributes The other html attributes of the script element
	 * @param boolean $absolute True if url must be absolute (containing siteUrl)
	 * @return string
	 */
	public static function css($resource, $attributes = [], $absolute = false) {
		return self::stylesheet ( self::getUrl ( $resource, $absolute ), $attributes );
	}

	/**
	 * Returns the image tag for inclusion.
	 *
	 * @param string $src The base path to the image
	 * @param array $attributes The other html attributes of the image element
	 * @param boolean $absolute True if url must be absolute (containing siteUrl)
	 * @return string
	 */
	public static function img($src, $attributes = [], $absolute = false) {
		return self::image ( self::getUrl ( $src, $absolute ), $attributes );
	}

	/**
	 * Returns the script inclusion for a javascript resource in **activeTheme**.
	 *
	 * @param string $resource The javascript resource to include
	 * @param array $attributes The other html attributes of the script element
	 * @param boolean $absolute True if url must be absolute (containing siteUrl)
	 * @return string
	 */
	public static function js_($resource, $attributes = [], $absolute = false) {
		return self::script ( self::getActiveThemeUrl ( $resource, $absolute ), $attributes );
	}

	/**
	 * Returns the css inclusion for a stylesheet resource in **activeTheme**.
	 *
	 * @param string $resource The css resource to include
	 * @param array $attributes The other html attributes of the script element
	 * @param boolean $absolute True if url must be absolute (containing siteUrl)
	 * @return string
	 */
	public static function css_($resource, $attributes = [], $absolute = false) {
		return self::stylesheet ( self::getActiveThemeUrl ( $resource, $absolute ), $attributes );
	}

	/**
	 * Returns the image tag for inclusion in **activeTheme**.
	 *
	 * @param string $src The base path to the image
	 * @param array $attributes The other html attributes of the image element
	 * @param boolean $absolute True if url must be absolute (containing siteUrl)
	 * @return string
	 */
	public static function img_($src, $attributes = [], $absolute = false) {
		return self::image ( self::getActiveThemeUrl ( $src, $absolute ), $attributes );
	}
}

<?php

namespace Ubiquity\utils\base;

/**
 * DateTime utilities
 * Ubiquity\utils\base$UDateTime
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.2
 *
 */
class UDateTime {
	const MYSQL_DATETIME_FORMAT = 'Y-m-d H:i:s';
	const MYSQL_DATE_FORMAT = 'Y-m-d';
	private static $locales = [ "fr" => [ "fr","fr_FR","fr_FR.UTF-8" ],"en" => [ "en","en_US","en_US.UTF-8" ] ];

	private static function setLocale($locale) {
		$locale = self::$locales [$locale]??self::$locales ["en"];
		setlocale ( LC_TIME, $locale [0], $locale [1], $locale [2] );
	}

	public static function secondsToTime($seconds) {
		$hours = floor ( $seconds / 3600 );
		$mins = floor ( $seconds / 60 % 60 );
		$secs = floor ( $seconds % 60 );
		return sprintf ( '%02d:%02d:%02d', $hours, $mins, $secs );
	}

	public static function mysqlDate(\DateTime $date) {
		return $date->format ( self::MYSQL_DATE_FORMAT );
	}

	public static function mysqlDateTime(\DateTime $datetime) {
		return $datetime->format ( self::MYSQL_DATETIME_FORMAT );
	}

	public static function longDate($date, $locale = "en") {
		self::setLocale ( $locale );
		return \date('l d F Y',\strtotime($date));
	}

	public static function shortDate($date, $locale = "en") {
		self::setLocale ( $locale );
		return \date('d/m/y',\strtotime($date));
	}

	public static function shortDatetime($datetime, $locale = "en") {
		self::setLocale ( $locale );
		return \date('c',\strtotime($date));
	}

	public static function longDatetime($datetime, $locale = "en") {
		self::setLocale ( $locale );
		return date('l d F Y, H:i:s', \strtotime ( $datetime ));
	}

	/**
	 *
	 * @param string|\DateTime $datetime
	 * @param boolean $full
	 * @return string
	 * @see http://stackoverflow.com/questions/1416697/converting-timestamp-to-time-ago-in-php-e-g-1-day-ago-2-days-ago
	 */
	public static function elapsed($datetime, $full = false) {
		$now = new \DateTime ();
		if (! $datetime instanceof \DateTime) {
			$ago = new \DateTime ( $datetime );
		} else {
			$ago = $datetime;
		}
		$diff = $now->diff ( $ago );

		$diff->w = floor ( $diff->d / 7 );
		$diff->d -= $diff->w * 7;

		$string = array ('y' => 'year','m' => 'month','w' => 'week','d' => 'day','h' => 'hour','i' => 'minute','s' => 'second' );
		foreach ( $string as $k => &$v ) {
			if ($diff->$k) {
				$v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
			} else {
				unset ( $string [$k] );
			}
		}

		if (! $full)
			$string = \array_slice ( $string, 0, 1 );
		return $string ? implode ( ', ', $string ) . ($diff->invert ? ' ago' : '') : 'just now';
	}
}


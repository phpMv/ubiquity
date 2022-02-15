<?php


namespace Ubiquity\utils\base;

/**
 * User agent detection.
 * 
 * Ubiquity\utils\base$UASystem
 * This class is part of Ubiquity
 * @author jc
 * @version 1.0.0
 *
 */
class UASystem {
	
	const BROWSERS=['MSIE'=>'Internet Explorer','Firefox'=>'Mozilla Firefox','Edg'=>'Microsoft Edge','OPR'=>'Opera','Chrome'=>'Google Chrome','Safari'=>'Apple Safari','Netscape'=>'Netscape'];

	const SYSTEMS=['linux'=>'linux','mac'=>'macintosh|mac os x','windows'=>'windows|win32'];

	private static $browserInfos;

	private static function getBrowserInfos() {
		$userAgent = $_SERVER['HTTP_USER_AGENT'];
		$browserName = 'Unknown';
		$platform = self::getPlatformFromUserAgent($userAgent);
		$version = '';

		foreach (self::BROWSERS as $k=>$name){
			if(\preg_match("/$k/i",$userAgent)) {
				$browserName = $name;
				$ub = $k;
				break;
			}
		}

		$known = ['Version', $ub, 'other'];
		$pattern = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
		\preg_match_all($pattern, $userAgent, $matches);

		$i = \count($matches['browser']);
		if ($i != 1) {
			if (\strripos($userAgent,'Version') < \strripos($userAgent,$ub)){
				$version= $matches['version'][0]??null;
			}
			else {
				$version= $matches['version'][1]??null;
			}
		}
		else {
			$version= $matches['version'][0]??null;
		}

		$version??='?';

		return [
			'userAgent' => $userAgent,
			'name'      => $browserName,
			'version'   => $version,
			'platform'  => $platform,
			'pattern'   => $pattern
		];
	}

	private static function getPlatformFromUserAgent(string $userAgent):string{
		$platform='Unknown';
		foreach (self::SYSTEMS as $name=>$reg){
			if (\preg_match("/$reg/i", $userAgent)) {
				$platform = $name;
			}
		}
		return $platform;
	}
	private static function _getBrowser():array{
		return self::$browserInfos??=self::getBrowserInfos();
	}
	public static function getBrowserComplete():string{
		$b=self::_getBrowser();
		return $b['name'].' '.$b['version'];
	}

	public static function getBrowserName():string{
		return self::_getBrowser()['name'];
	}

	public static function getBrowserVersion():string{
		return self::_getBrowser()['version'];
	}

	public static function getPlatform():string{
		return self::_getBrowser()['platform'];
	}
}
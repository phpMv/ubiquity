<?php

namespace Ubiquity\utils\base;

class UDateTime {
	public static function secondsToTime($seconds){
		$hours = floor($seconds / 3600);
		$mins = floor($seconds / 60 % 60);
		$secs = floor($seconds % 60);
		return sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
	}
}


<?php

namespace Ubiquity\utils\git;

class GitFileStatus {
	public static $UNTRACKED="untracked";
	public static $MODIFIED="modified";
	public static $DELETED="deleted";
	public static $NONE="";
	public static function getIcon($status){
		switch ($status){
			case self::$UNTRACKED:
				$icon="blue question";
				break;
			case self::$MODIFIED:
				$icon="green edit";
				break;
			case self::$DELETED:
				$icon="red remove";
				break;
			default:
				$icon="red question";
				break;
		}
		return $icon;
	}
}


<?php

namespace Ubiquity\contents\validation\validators;

class ConstraintViolationViewer {
	const SEVERITY_ICONS=[
			'default'=>['icon'=>'thumbtack','type'=>''],
			'info'=>['icon'=>'info circle','type'=>'info'],
			'warning'=>['icon'=>'exclamation circle','type'=>'warning'],
			'error'=>['icon'=>'exclamation triangle','type'=>'error']
	];
	
	private static function getValue($severity){
		if(isset($severity) && isset(self::SEVERITY_ICONS[$severity])){
			return self::SEVERITY_ICONS[$severity];
		}
		return self::SEVERITY_ICONS['default'];
	}
	public static function getIcon($severity){
		return self::getValue($severity)['icon'];
	}
	
	public static function getType($severity){
		return self::getValue($severity)['type'];
	}
}


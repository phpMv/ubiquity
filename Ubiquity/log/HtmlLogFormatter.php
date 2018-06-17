<?php

namespace Ubiquity\log;

class HtmlLogFormatter {
	private static $icons=[100=>"",200=>"blue info circle",300=>"orange warning circle",400=>"exclamation triangle",500=>"thumbs down outline",550=>"thumbs down",600=>"ban"];
	private static $format=[100=>"",200=>"info",300=>"warning",400=>"error",500=>"error",550=>"error",600=>"error"];

	public static function getIcon(LogMessage $message){
		return self::$icons[$message->getLevel()];
	}
	
	public static function getFormat(LogMessage $message){
		return self::$format[$message->getLevel()];
	}
}

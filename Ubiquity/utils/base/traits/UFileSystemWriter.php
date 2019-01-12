<?php

namespace Ubiquity\utils\base\traits;

trait UFileSystemWriter {
	
	public static function openReplaceInTemplateFile($source, $keyAndValues) {
		if (\file_exists($source)) {
			$str=\file_get_contents($source);
			return self::replaceFromTemplate($str, $keyAndValues);
		}
		return false;
	}
	
	public static function openReplaceWriteFromTemplateFile($source, $destination, $keyAndValues) {
		if (($str=self::openReplaceInTemplateFile($source, $keyAndValues))) {
			return \file_put_contents($destination, $str, LOCK_EX);
		}
		return false;
	}
	
	public static function replaceFromTemplate($content, $keyAndValues) {
		array_walk($keyAndValues, function (&$item) {
			if (\is_array($item))
				$item=\implode("\n", $item);
		});
			$str=\str_replace(array_keys($keyAndValues), array_values($keyAndValues), $content);
			return $str;
	}
	
	public static function replaceWriteFromContent($content, $destination, $keyAndValues) {
		return \file_put_contents($destination, self::replaceFromTemplate($content, $keyAndValues), LOCK_EX);
	}
	
	public static function save($filename,$content,$flags=LOCK_EX){
		return \file_put_contents($filename, $content, $flags);
	}
	
	public static function xcopy($source, $dest, $permissions = 0755){
		if (is_link($source)) {
			return symlink(readlink($source), $dest);
		}
		if (is_file($source)) {
			return copy($source, $dest);
		}
		if (!is_dir($dest)) {
			mkdir($dest, $permissions,true);
		}
		$dir = dir($source);
		while (false !== $entry = $dir->read()) {
			if ($entry == '.' || $entry == '..') {
				continue;
			}
			self::xcopy("$source/$entry", "$dest/$entry", $permissions);
		}
		$dir->close();
		return true;
	}
}


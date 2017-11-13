<?php

namespace micro\utils;

/**
 * @author jc
 * @version 1.0.0.1
 */
class FsUtils {

	public static function glob_recursive($pattern, $flags=0) {
		$files=glob($pattern, $flags);
		foreach ( glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir ) {
			$files=array_merge($files, self::glob_recursive($dir . '/' . basename($pattern), $flags));
		}
		return $files;
	}

	public static function deleteAllFilesFromFolder($folder) {
		$files=glob($folder . '/*');
		foreach ( $files as $file ) {
			if (is_file($file))
				unlink($file);
		}
	}

	public static function safeMkdir($dir){
		if(!is_dir($dir))
			return mkdir($dir,0777,true);
		return true;
	}

	public static function cleanPathname($path){
		if(StrUtils::isNotNull($path)){
			if(DS==="/")
				$path=\str_replace("\\", DS, $path);
			else
				$path=\str_replace("/", DS, $path);
			$path=\str_replace(DS.DS, DS, $path);
			if(!StrUtils::endswith($path, DS)){
				$path=$path.DS;
			}
		}
		return $path;
	}
}
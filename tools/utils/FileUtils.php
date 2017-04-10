<?php
class FileUtils {
	public static function deleteAllFilesFromFolder($folder){
		$files = glob($folder.'/*');
		foreach($files as $file){
			if(is_file($file))
				unlink($file);
		}
	}

	public static function openFile($filename){
		if(file_exists($filename)){
			return file_get_contents($filename);
		}
		return false;
	}

	public static function writeFile($filename,$data){
		return file_put_contents($filename,$data);
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

	public static function delTree($dir) {
		$files = array_diff(scandir($dir), array('.','..'));
		foreach ($files as $file) {
			(is_dir("$dir/$file")) ? self::delTree("$dir/$file") : unlink("$dir/$file");
		}
		return rmdir($dir);
	}

	public static function safeMkdir($dir){
		if(!is_dir($dir))
			return mkdir($dir,0777,true);
	}
}
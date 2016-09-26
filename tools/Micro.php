<?php
class Micro {

	public static function downloadZip($url,$zipFile="tmp/tmp.zip"){
		$zipResource = fopen($zipFile, "w");
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FAILONERROR, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER,true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_FILE, $zipResource);
		$page = curl_exec($ch);
		if(!$page) {
			echo "Error :- ".curl_error($ch);
		}
		curl_close($ch);
	}

	public static function unzip($zipFile,$extractPath="./"){
		$zip = new \ZipArchive();
		if($zip->open($zipFile) != "true"){
			echo "Error :- Unable to open the Zip File";
		}
		/* Extract Zip File */
		$zip->extractTo($extractPath);
		$zip->close();
	}

	public static function xcopy($source, $dest, $permissions = 0755)
	{
		// Check for symlinks
		if (is_link($source)) {
			return symlink(readlink($source), $dest);
		}

		// Simple copy for a file
		if (is_file($source)) {
			return copy($source, $dest);
		}

		// Make destination directory
		if (!is_dir($dest)) {
			mkdir($dest, $permissions);
		}

		// Loop through the folder
		$dir = dir($source);
		while (false !== $entry = $dir->read()) {
			// Skip pointers
			if ($entry == '.' || $entry == '..') {
				continue;
			}

			// Deep copy directories
			xcopy("$source/$entry", "$dest/$entry", $permissions);
		}

		// Clean up
		$dir->close();
		return true;
	}

	public static function create($projectName){
		if(mkdir($projectName)==true){
			chdir($projectName);
			echo "Downloading micro.git from https://github.com/phpMv/...\n";
			mkdir("tmp");
			self::downloadZip("https://github.com/phpMv/micro.git","tmp/tmp.zip");
			echo "Extraction des fichiers...\n";
			self::unzip("tmp/tmp.zip");
		}
	}
}

Micro::create($argv[1]);
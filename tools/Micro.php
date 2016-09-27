<?php
class Micro {

	public static function downloadZip($url,$zipFile="tmp/tmp.zip"){
		$f = file_put_contents($zipFile, fopen($url, 'r'), LOCK_EX);
	if(FALSE === $f)
		die("Couldn't write to file.");
	else{
		echo $f." téléchargés.\n";
	}
	}

	public static function unzip($zipFile,$extractPath="."){
		$zip = new \ZipArchive();
		if (! $zip) {
			echo "<br>Could not make ZipArchive object.";
			exit;
		}
		if($zip->open($zipFile) !== TRUE){
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
			self::xcopy("$source/$entry", "$dest/$entry", $permissions);
		}

		// Clean up
		$dir->close();
		return true;
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

	public static function replaceAll($array,$subject){
		return str_replace(array_keys($array), array_values($array), $subject);
	}

	public static function openReplaceWrite($source,$destination,$keyAndValues){
		$str=self::openFile($source);
		$str=self::replaceAll($keyAndValues,$str);
		return self::writeFile($destination,$str);
	}

	private static function getOption($option,$default=NULL){
		$options=getopt("b::",array("dbName::"));
		if(array_key_exists($option, $options))
			$option=$options["dbName"];
		else if(isset($default)===true){
			$option=$default;
		}else
			$option="";
		return $option;
	}

	public static function create($projectName){
		if(mkdir($projectName)==true){
			chdir($projectName);
			echo "Downloading micro.git from https://github.com/phpMv/...\n";
			mkdir("tmp");
			self::downloadZip("https://github.com/phpMv/micro/archive/master.zip","tmp/tmp.zip");
			echo "Files extraction...\n";
			self::unzip("tmp/tmp.zip","tmp/");
			mkdir("app");
			echo "Files copy...\n";
			self::xcopy("tmp/micro-master/micro/","app/micro");
			echo "Config files creation...";
			self::openReplaceWrite("tmp/micro-master/project-files/.htaccess", getcwd()."/.htaccess", array("%rewriteBase%"=>$projectName));
			self::openReplaceWrite("tmp/micro-master/project-files/app/config.php", "app/config.php", array(
					"%documentRoot%"=>"","%siteUrl%"=>"http://127.0.0.1/".$projectName."/",
					"%dbName%"=>self::getOption("dbName",$projectName),
			));
			self::xcopy("tmp/micro-master/project-files/index.php", "index.php");
			echo "project {$projectName} successfully created.\n";
		}
	}
}

Micro::create($argv[1]);
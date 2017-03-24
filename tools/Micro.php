<?php
use micro\controllers\Autoloader;
use micro\utils\StrUtils;
include 'ModelsCreator.php';
class Micro {
	private static $configOptions;
	public static function downloadZip($url,$zipFile="tmp/tmp.zip"){
		$f = file_put_contents($zipFile, fopen($url, 'r'), LOCK_EX);
	if(FALSE === $f)
		die("Couldn't write to file.");
	else{
		echo $f." downloaded.\n";
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

	public static function delTree($dir) {
		$files = array_diff(scandir($dir), array('.','..'));
		foreach ($files as $file) {
			(is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
		}
		return rmdir($dir);
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

	private static function getOption($option,$longOption,$default=NULL){
		$options=self::parseArguments();
		if(array_key_exists($option, $options)){
			$option=$options[$option];
		}else if(array_key_exists($longOption, $options)){
			$option=$options[$longOption];
		}
		else if(isset($default)===true){
			$option=$default;
		}else
			$option="";
		return $option;
	}

	public static function create($projectName){
		$arguments=[
				["b","dbName",$projectName],
				["r","documentRoot","Main"],
				["s","serverName","127.0.0.1"],
				["p","port","3306"],
				["u","user","root"],
				["w","password",""],
				["m","all-models",true],
		];
		if(mkdir($projectName)==true){
			chdir($projectName);
			echo "Downloading micro.git from https://github.com/phpMv/...\n";
			mkdir("tmp");
			self::downloadZip("https://github.com/phpMv/micro/archive/master.zip","tmp/tmp.zip");
			echo "Files extraction...\n";
			self::unzip("tmp/tmp.zip","tmp/");
			mkdir("app");
			define('ROOT', realpath('./app').DS);
			echo "Files copy...\n";
			self::xcopy("tmp/micro-master/micro/","app/micro");
			echo "Config files creation...\n";
			self::openReplaceWrite("tmp/micro-master/project-files/.htaccess", getcwd()."/.htaccess", array("%rewriteBase%"=>$projectName));
			self::$configOptions=["%siteUrl%"=>"http://127.0.0.1/".$projectName."/"];
			foreach ($arguments as $argument){
				self::$configOptions["%".$argument[1]."%"]=self::getOption($argument[0], $argument[1],$argument[2]);
			}
			self::openReplaceWrite("tmp/micro-master/project-files/app/config.php", "app/config.php", self::$configOptions);
			self::xcopy("tmp/micro-master/project-files/index.php", "index.php");
			require_once 'app/micro/controllers/Autoloader.php';
			Autoloader::register();
			if(StrUtils::isBooleanTrue(self::$configOptions["%all-models%"]))
				ModelsCreator::create();
			echo "deleting temporary files...\n";
			self::delTree("tmp");
			echo "project `{$projectName}` successfully created.\n";
		}
	}

	private static function parseArguments(){
		global $argv;
		array_shift($argv);
		$out = array();
		foreach($argv as $arg){
			if(substr($arg, 0, 2) == '--'){
				preg_match ("/\=|\:|\ /", $arg, $matches, PREG_OFFSET_CAPTURE);
				$eqPos=$matches[0][1];
				//$eqPos = strpos($arg, '=');
				if($eqPos === false){
					$key = substr($arg, 2);
					$out[$key] = isset($out[$key]) ? $out[$key] : true;
				}
				else{
					$key = substr($arg, 2, $eqPos - 2);
					$out[$key] = substr($arg, $eqPos + 1);
				}
			}
			else if(substr($arg, 0, 1) == '-'){
				if(substr($arg, 2, 1) == '='||substr($arg, 2, 1) == ':' || substr($arg, 2, 1) == ' '){
					$key = substr($arg, 1, 1);
					$out[$key] = substr($arg, 3);
				}
				else{
					$chars = str_split(substr($arg, 1));
					foreach($chars as $char){
						$key = $char;
						$out[$key] = isset($out[$key]) ? $out[$key] : true;
					}
				}
			}
			else{
				$out[] = $arg;
			}
		}
		return $out;
	}
	public static function init($command){
		global $argv;
		switch ($command) {
			case "project":case "create-project":
			self::create($argv[2]);
			break;
			case "all-models":
				ModelsCreator::create();
			default:
				;
			break;
		}

	}
}
error_reporting(E_ALL);

define('DS', DIRECTORY_SEPARATOR);

Micro::init($argv[1]);
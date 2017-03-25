<?php
use micro\controllers\Autoloader;
use micro\utils\StrUtils;
include 'ModelsCreator.php';
include 'Console.php';
class Micro {
	private static $configOptions;
	private static $composer=["require"=>["twig/twig"=>"~1.0"]];

	public static function downloadZip($url,$zipFile="tmp/tmp.zip"){
		$f = file_put_contents($zipFile, fopen($url, 'r'), LOCK_EX);
	if(FALSE === $f)
		die("Couldn't write to file.");
	else{
		echo $f." downloaded.\n";
	}
	}

	public static function createComposerFile(){
		$composer=json_encode(self::$composer);
		echo "Composer file creation...\n";
		self::writeFile("composer.json", $composer);
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
			(is_dir("$dir/$file")) ? self::delTree("$dir/$file") : unlink("$dir/$file");
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

	private static function getOption($options,$option,$longOption,$default=NULL){
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

	public static function create($projectName,$force=false){
		$arguments=[
				["b","dbName",$projectName],
				["r","documentRoot","Main"],
				["s","serverName","127.0.0.1"],
				["p","port","3306"],
				["u","user","root"],
				["w","password",""],
				["m","all-models",false],
		];
		if(!is_dir($projectName) || $force){
			if(!$force)
				self::safeMkdir($projectName);
			chdir($projectName);
			echo "Downloading micro.git from https://github.com/phpMv/...\n";
			self::safeMkdir("tmp");self::safeMkdir(".micro");
			self::downloadZip("https://github.com/phpMv/micro/archive/master.zip","tmp/tmp.zip");
			echo "Files extraction...\n";
			self::unzip("tmp/tmp.zip","tmp/");
			self::safeMkdir("app");
			define('ROOT', realpath('./app').DS);
			echo "Files copy...\n";
			self::xcopy("tmp/micro-master/micro/","app/micro");
			echo "Config files creation...\n";
			self::openReplaceWrite("tmp/micro-master/project-files/.htaccess", getcwd()."/.htaccess", array("%rewriteBase%"=>$projectName));
			self::$configOptions=["%siteUrl%"=>"http://127.0.0.1/".$projectName."/"];
			$options=self::parseArguments();
			foreach ($arguments as $argument){
				self::$configOptions["%".$argument[1]."%"]=self::getOption($options,$argument[0], $argument[1],$argument[2]);
			}
			self::openReplaceWrite("tmp/micro-master/project-files/app/config.php", "app/config.php", self::$configOptions);
			self::xcopy("tmp/micro-master/project-files/index.php", "index.php");
			require_once 'app/micro/controllers/Autoloader.php';
			Autoloader::register();
			echo "#".self::$configOptions["%all-models%"]."#";
			if(StrUtils::isBooleanTrue(self::$configOptions["%all-models%"]))
				ModelsCreator::create();
			self::createController("Main","\n\techo '<h1>Micro framework</h1>It works !';\n");
			echo "deleting temporary files...\n";
			self::delTree("tmp");
			self::createComposerFile();
			$answer=Console::question("Do you want to run composer ?",["y","n"]);
			if(Console::isYes($answer))
				system("composer -install");
			echo "project `{$projectName}` successfully created.\n";
		}else{
			echo "The {$projectName} folder already exists !\n";
			$answer=Console::question("Would you like to continue ?",["y","n"]);
			if(Console::isYes($answer)){
				self::create($projectName,true);
			}else
				die();
		}
	}

	public static function createController($controllerName,$indexContent=null,$force=false){
		$controllerName=ucfirst($controllerName);
		self::safeMkdir("app/controllers");
		$filename="app/controllers/{$controllerName}.php";
		if(file_exists($filename)){
			$answer=Console::question("The file {$filename} exists.\nWould you like to replace it?",["y","n"]);
			if(Console::isYes($answer))
				self::createController($controllerName,$indexContent,true);
		}else{
			echo "Creating the Controller {$controllerName} at the location {$filename}\n";
			self::openReplaceWrite("tmp/micro-master/project-files/templates/controller.tpl", $filename, ["%controllerName%"=>$controllerName,"%indexContent%",$indexContent]);
		}
	}

	private static function safeMkdir($dir){
		if(!is_dir($dir))
			return mkdir($dir);
	}

	private static function setDir($dir=null){
		if(file_exists($dir) && is_dir($dir)){
			$microDir=$dir.DIRECTORY_SEPARATOR.".micro";
			if(file_exists($microDir) && is_dir($microDir)){
				chdir($dir);
				echo "The project folder is {$dir}\n";
				return true;
			}
		}
		$newDir=dirname($dir);
		if($newDir===$dir)
			return false;
		else
			return self::setDir($newDir);
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
			case "project":case "create-project":case "new":
			self::create($argv[2]);
			break;
			case "all-models":
				self::_init();
				ModelsCreator::create();
				break;
			case "model":
				self::_init();
				ModelsCreator::create($argv[2]);
				break;
			case "controller":
				self::_init();
				self::createController($argv[2]);
				break;
			default:
				;
			break;
		}
	}
	private static function _init(){
		if(!self::setDir(getcwd())){
			echo "Failed to locate project root folder\n";
			echo "A Micro project must contain the .micro empty folder.\n";
			die();
		}
		define('ROOT', realpath('./app').DS);

		require_once 'app/micro/controllers/Autoloader.php';
		Autoloader::register();
	}
}
error_reporting(E_ALL);

define('DS', DIRECTORY_SEPARATOR);

Micro::init($argv[1]);
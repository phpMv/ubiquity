<?php
use micro\controllers\Autoloader;
use micro\utils\StrUtils;
include 'ModelsCreator.php';
include 'Console.php';
class Micro {
	private static $configOptions;
	private static $composer=["require"=>["twig/twig"=>"~1.0","mindplay/annotations"=>"~1.2"]];
	private static $toolsConfig;
	private static $indexContent="\n\t\$this->loadView('index.html');\n";
	private static $mainViewTemplate="index.html";

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
			mkdir($dest, $permissions,true);
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
		array_walk($array, function(&$item){if(is_array($item)) $item=implode("\n", $item);});
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
		self::$toolsConfig=include("toolsConfig.php");
		$arguments=[
				["b","dbName",$projectName],
				["r","documentRoot","Main"],
				["s","serverName","127.0.0.1"],
				["p","port","3306"],
				["u","user","root"],
				["w","password",""],
				["m","all-models",false],
				["q","phpmv",false],
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
			self::safeMkdir("app/views/main");
			self::safeMkdir("app/controllers");
			define('ROOT', realpath('./app').DS);
			echo "Files copy...\n";
			self::xcopy("tmp/micro-master/micro/","app/micro");
			self::xcopy("tmp/micro-master/project-files/templates", "app/micro/tools/templates");
			self::xcopy("tmp/micro-master/project-files/app/controllers/ControllerBase.php", "app/controllers/ControllerBase.php");


			echo "Config files creation...\n";
			self::openReplaceWrite("tmp/micro-master/project-files/.htaccess", getcwd()."/.htaccess", array("%rewriteBase%"=>$projectName));
			self::$configOptions=["%siteUrl%"=>"http://127.0.0.1/".$projectName."/"];
			self::$configOptions["%projectName%"]=$projectName;
			self::$configOptions["%injections%"]="";
			self::$configOptions["%cssFiles%"]=[];
			self::$configOptions["%jsFiles%"]=[];
			$options=self::parseArguments();
			foreach ($arguments as $argument){
				self::$configOptions["%".$argument[1]."%"]=self::getOption($options,$argument[0], $argument[1],$argument[2]);
			}
			self::showConfigOptions();

			self::includePhpmv();

			self::openReplaceWrite("tmp/micro-master/project-files/templates/config.tpl", "app/config.php", self::$configOptions);
			self::xcopy("tmp/micro-master/project-files/index.php", "index.php");
			self::openReplaceWrite("tmp/micro-master/project-files/templates/vHeader.tpl", "app/views/main/vHeader.html", self::$configOptions);
			self::openReplaceWrite("tmp/micro-master/project-files/templates/vFooter.tpl", "app/views/main/vFooter.html", self::$configOptions);

			self::createComposerFile();
			$answer=Console::question("Do you want to run composer install ?",["y","n"]);
			if(Console::isYes($answer)){
				system("composer install");
				require_once ROOT.'./../vendor/autoload.php';
			}
			require_once 'app/micro/controllers/Autoloader.php';
			$config=require_once 'app/config.php';
			Autoloader::register($config);

			self::createController("Main",self::$indexContent);
			self::xcopy("tmp/micro-master/project-files/app/views/".self::$mainViewTemplate, "app/views/index.html");
			echo "deleting temporary files...\n";
			self::delTree("tmp");

			if(StrUtils::isBooleanTrue(self::$configOptions["%all-models%"]))
				ModelsCreator::create();
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
	private static function includePhpmv(){
		if(self::$configOptions["%phpmv%"]!==false){
			$phpmv=self::$configOptions["%phpmv%"];
			switch ($phpmv){
				case "bootstrap":case "semantic":
					self::$configOptions["%injections%"]="\"jquery\"=>function(){
					\t\t\$jquery=new Ajax\php\micro\JsUtils([\"defer\"=>true]);
					\t\t\$jquery->{$phpmv}(new Ajax\\".ucfirst($phpmv)."());
					\t\treturn \$jquery;
					\t}";
					break;
				default:
					throw new Exception($phpmv." is not a valid option for phpMv-UI.");
					break;
			}
			self::$composer["require"]["phpmv/php-mv-ui"]="dev-master";
			if($phpmv==="bootstrap"){
				self::$configOptions["%cssFiles%"][]=self::includeCss(self::$toolsConfig["cdn"]["bootstrap"]["css"]);
				self::$configOptions["%jsFiles%"][]=self::includeJs(self::$toolsConfig["cdn"]["jquery"]);
				self::$configOptions["%jsFiles%"][]=self::includeJs(self::$toolsConfig["cdn"]["bootstrap"]["js"]);
				self::$mainViewTemplate="bootstrap.html";
			}
			elseif($phpmv==="semantic"){
				self::$configOptions["%cssFiles%"][]=self::includeCss(self::$toolsConfig["cdn"]["semantic"]["css"]);
				self::$configOptions["%jsFiles%"][]=self::includeJs(self::$toolsConfig["cdn"]["jquery"]);
				self::$configOptions["%jsFiles%"][]=self::includeJs(self::$toolsConfig["cdn"]["semantic"]["js"]);
				self::$indexContent='
		$semantic=$this->jquery->semantic();
		$semantic->htmlHeader("header",1,"Micro framework");
		$bt=$semantic->htmlButton("btTest","Semantic-UI Button");
		$bt->onClick("$(\'#test\').html(\'It works with Semantic-UI too !\');");
		$this->jquery->compile($this->view);
		$this->loadView("index.html");';
				self::$mainViewTemplate="semantic.html";
			}
		}
	}

	private static function includeCss($filename){
		return "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$filename}\">";
	}

	private static function includeJs($filename){
		return "<script src=\"{$filename}\"></script>";
	}

	private static function showConfigOptions(){
		$output = implode(', ', array_map(
		function ($v, $k) {if(is_array($v))
			$v=implode(",",$v );
			return sprintf("%s='%s'", str_ireplace("%", "", $k), $v); },
		self::$configOptions,
		array_keys(self::$configOptions)
		));
		echo "command line arguments :\n";
		echo $output."\n";
	}

	public static function createController($controllerName,$indexContent=null,$force=false){
		$controllerName=ucfirst($controllerName);
		self::safeMkdir("app/controllers");
		$filename="app/controllers/{$controllerName}.php";
		if(file_exists($filename) && !$force){
			$answer=Console::question("The file {$filename} exists.\nWould you like to replace it?",["y","n"]);
			if(Console::isYes($answer))
				self::createController($controllerName,$indexContent,true);
		}else{
			echo "Creating the Controller {$controllerName} at the location {$filename}\n";
			self::openReplaceWrite("app/micro/tools/templates/controller.tpl", $filename, ["%controllerName%"=>$controllerName,"%indexContent%"=>$indexContent]);
		}
	}

	private static function safeMkdir($dir){
		if(!is_dir($dir))
			return mkdir($dir,0777,true);
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
		$config=require_once 'app/config.php';
		require_once 'app/micro/controllers/Autoloader.php';
		Autoloader::register($config);
	}
}
error_reporting(E_ALL);

define('DS', DIRECTORY_SEPARATOR);

Micro::init($argv[1]);
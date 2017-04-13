<?php
use micro\controllers\Autoloader;
use micro\utils\StrUtils;
use micro\cache\CacheManager;
include 'ModelsCreator.php';
include 'Console.php';
include 'Command.php';
include 'utils/FileUtils.php';

class Micro {
	private static $version="1.0.3";
	private static $appName="#micro devtools";
	private static $configOptions;
	private static $toolsConfig;
	private static $indexContent="\n\t\$this->loadView('index.html');\n";
	private static $mainViewTemplate="index.html";
	private static $commands=["new"=>["Creates a new project"]];

	public static function downloadZip($url,$zipFile="tmp/tmp.zip"){
		$f = file_put_contents($zipFile, fopen($url, 'r'), LOCK_EX);
	if(FALSE === $f)
		die("Couldn't write to file.");
	else{
		echo $f." downloaded.\n";
	}
	}

	public static function createComposerFile(){
		$composer=json_encode(self::$toolsConfig["composer"]);
		echo "Composer file creation...\n";
		FileUtils::writeFile("composer.json", $composer);
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
		$zip->extractTo($extractPath);
		$zip->close();
	}

	public static function replaceAll($array,$subject){
		array_walk($array, function(&$item){if(is_array($item)) $item=implode("\n", $item);});
		return str_replace(array_keys($array), array_values($array), $subject);
	}

	public static function openReplaceWrite($source,$destination,$keyAndValues){
		$str=FileUtils::openFile($source);
		$str=self::replaceAll($keyAndValues,$str);
		return FileUtils::writeFile($destination,$str);
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

	public static function create($projectName,$options,$force=false){
		self::$toolsConfig=include("toolsConfig.php");
		$arguments=[
				["b","dbName",$projectName],
				["s","serverName","127.0.0.1"],
				["p","port","3306"],
				["u","user","root"],
				["w","password",""],
				["m","all-models",false],
				["q","phpmv",false],
		];
		if(!is_dir($projectName) || $force){
			if(!$force)
				FileUtils::safeMkdir($projectName);
			chdir($projectName);
			echo "Downloading micro.git from https://github.com/phpMv/...\n";
			FileUtils::safeMkdir("tmp");FileUtils::safeMkdir(".micro");
			self::downloadZip("https://github.com/phpMv/micro/archive/master.zip","tmp/tmp.zip");
			echo "Files extraction...\n";
			self::unzip("tmp/tmp.zip","tmp/");
			FileUtils::safeMkdir("app");
			FileUtils::safeMkdir("app/views/main");
			FileUtils::safeMkdir("app/controllers");
			FileUtils::safeMkdir("app/config");
			define('ROOT', realpath('./app').DS);
			echo "Files copy...\n";
			FileUtils::xcopy("tmp/micro-master/micro/","app/micro");
			FileUtils::xcopy("tmp/micro-master/project-files/templates", "app/micro/tools/templates");
			FileUtils::xcopy("tmp/micro-master/project-files/app/controllers/ControllerBase.php", "app/controllers/ControllerBase.php");


			echo "Config files creation...\n";
			self::openReplaceWrite("tmp/micro-master/project-files/.htaccess", getcwd()."/.htaccess", array("%rewriteBase%"=>$projectName));
			self::$configOptions=["%siteUrl%"=>"http://127.0.0.1/".$projectName."/"];
			self::$configOptions["%projectName%"]=$projectName;
			self::$configOptions["%injections%"]="";
			self::$configOptions["%cssFiles%"]=[];
			self::$configOptions["%jsFiles%"]=[];
			foreach ($arguments as $argument){
				self::$configOptions["%".$argument[1]."%"]=self::getOption($options,$argument[0], $argument[1],$argument[2]);
			}
			self::showConfigOptions();

			self::includePhpmv();

			self::openReplaceWrite("tmp/micro-master/project-files/templates/config.tpl", "app/config/config.php", self::$configOptions);
			FileUtils::xcopy("tmp/micro-master/project-files/templates/services.tpl", "app/config/services.php");
			FileUtils::xcopy("tmp/micro-master/project-files/index.php", "index.php");
			self::openReplaceWrite("tmp/micro-master/project-files/templates/vHeader.tpl", "app/views/main/vHeader.html", self::$configOptions);
			self::openReplaceWrite("tmp/micro-master/project-files/templates/vFooter.tpl", "app/views/main/vFooter.html", self::$configOptions);

			self::createComposerFile();
			$answer=Console::question("Do you want to run composer install ?",["y","n"]);
			if(Console::isYes($answer)){
				system("composer install");
				require_once ROOT.'./../vendor/autoload.php';
			}
			require_once 'app/micro/controllers/Autoloader.php';
			$config=require_once 'app/config/config.php';
			Autoloader::register($config);

			self::createController($config,"Main",self::$indexContent);
			FileUtils::xcopy("tmp/micro-master/project-files/app/views/".self::$mainViewTemplate, "app/views/index.html");
			echo "deleting temporary files...\n";
			FileUtils::delTree("tmp");

			if(StrUtils::isBooleanTrue(self::$configOptions["%all-models%"]))
				ModelsCreator::create($config);
			echo "project `{$projectName}` successfully created.\n";
		}else{
			echo "The {$projectName} folder already exists !\n";
			$answer=Console::question("Would you like to continue ?",["y","n"]);
			if(Console::isYes($answer)){
				self::create($projectName,$options,true);
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
			self::$toolsConfig["composer"]["require"]["phpmv/php-mv-ui"]="dev-master";
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

	public static function createController($config,$controllerName,$indexContent=null,$force=false){
		$controllerName=ucfirst($controllerName);
		FileUtils::safeMkdir("app/controllers");
		$filename="app/controllers/{$controllerName}.php";
		if(file_exists($filename) && !$force){
			$answer=Console::question("The file {$filename} exists.\nWould you like to replace it?",["y","n"]);
			if(Console::isYes($answer))
				self::createController($config,$controllerName,$indexContent,true);
		}else{
			echo "Creating the Controller {$controllerName} at the location {$filename}\n";
			$namespace="";
			if(isset($config["mvcNS"]["controllers"]) && $config["mvcNS"]["controllers"]!=="")
				$namespace="namespace ".$config["mvcNS"]["controllers"].";";
			self::openReplaceWrite("app/micro/tools/templates/controller.tpl", $filename, ["%controllerName%"=>$controllerName,"%indexContent%"=>$indexContent,"%namespace%"=>$namespace]);
		}
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
		register_shutdown_function(array("Micro","error"));
		$what=@$argv[2];
		$options=self::parseArguments();
		switch ($command) {
			case "project":case "create-project":case "new":
			self::create($what,$options);
			break;
			case "all-models":case "create-all-models":
				$config=self::_init();
				ModelsCreator::create($config);
				break;
			case "model":case "create-model":
				self::_init();
				ModelsCreator::create($config,$what);
				break;
			case "controller":case "create-controller":
				$config=self::_init();
				self::createController($config,$what);
				break;
			case "clear-cache":
				$type=self::getOption($options, "t", "type","all");
				$config=self::_init();
				CacheManager::clearCache($config,$type);
				break;
			case "init-cache":
				$type=self::getOption($options, "t", "type","all");
				$config=self::_init();
				CacheManager::initCache($config,$type);
				break;
			default:
				self::info();
			break;
		}
	}

	private static function info(){
		echo self::$appName." (".self::$version.")\n";
		$commands=Command::getCommands();
		foreach ($commands as $command){
			echo $command->longString();
			echo "\n";
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
		require_once ROOT.'micro/controllers/Autoloader.php';
		require_once ROOT.'./../vendor/autoload.php';
		Autoloader::register($config);
		return $config;
	}

	public static function error(){
		/*$last_error = error_get_last();
		if ($last_error['type'] === E_ERROR) {
			Startup::errorHandler(E_ERROR, $last_error['message'], $last_error['file'], $last_error['line']);
		}*/
	}
}
error_reporting(E_ALL);

define('DS', DIRECTORY_SEPARATOR);

Micro::init(@$argv[1]);
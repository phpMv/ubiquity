<?php
include_once 'Parameter.php';
class Command {
	protected $name;
	protected $description;
	protected $value;
	protected $aliases;
	protected $parameters;
	public function __construct($name,$value,$description,$aliases=[],$parameters=[]){
		$this->name=$name;
		$this->value=$value;
		$this->description=$description;
		$this->aliases=$aliases;
		$this->parameters=$parameters;
	}

	public function simpleString(){
		return "\t".$this->name." [".$this->value."]\t\t".$this->description;
	}

	public function longString(){
		$dec="\t";
		$result= "\n".$this->name." [".$this->value."] =>";
		$result.="\n".$dec."* ".$this->description;
		if(sizeof($this->aliases)>0){
			$result.="\n".$dec."* Aliases :";
			$result.=" ".implode(",", $this->aliases);
		}
		if(sizeof($this->parameters)>0){
			$result.="\n".$dec."* Parameters :";
			foreach ($this->parameters as $param=>$content){
				$result.="\n".$dec."\t-".$param;
				$result.=$content."\n";
			}
		}
		return $result;
	}

	public static function getInfo($command){

	}

	public static function project(){
		return new Command("project","projectName" ,"Creates a new #micro project.",["new","create-project"],[
				"b"=>Parameter::create("dbName", "Sets the database name.", []),
				"s"=>Parameter::create("serverName", "Defines the db server address.", [],"127.0.0.1"),
				"p"=>Parameter::create("port", "Defines the db server port.", [],"3306"),
				"u"=>Parameter::create("user", "Defines the db server user.", [],"root"),
				"w"=>Parameter::create("password", "Defines the db server password.", [],""),
				"q"=>Parameter::create("phpmv", "Integrates phpmv-UI Toolkit.", ["semantic","bootstrap","ui"],""),
				"m"=>Parameter::create("all-models", "Creates all models from database.", ["semantic","bootstrap","ui"],"")
		]);
	}

	public static function controller(){
		return new Command("controller","controllerName", "Creates a new controller.",["create-controller"]);
	}

	public static function model(){
		return new Command("model", "tableName","Generates a new model.",["create-model"]);
	}

	public static function allModels(){
		return new Command("all-models", "","Generates all models from database.",["create-all-models"]);
	}

	public static function clearCache(){
		return new Command("clear-cache", "","Clear models cache.",[],["a"=>Parameter::create("all", "Clear annotations and models cache.", ["true","false"])]);
	}

	public static function initCache(){
		return new Command("init-cache", "","Creates the cache for models.",[],[]);
	}

	public static function getCommands(){
		return [self::project(),self::controller(),self::model(),self::allModels(),self::clearCache(),self::initCache()];
	}
}
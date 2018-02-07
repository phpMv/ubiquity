<?php

namespace Ubiquity\controllers\admin\popo;

class Route {
	private $path;
	private $controller;
	private $action;
	private $parameters=[];
	private $cache;
	private $duration;
	private $name;
	private $methods;
	private $id;
	private $messages;

	public function __construct($path="",$array=[]){
		$this->messages=[];
		$this->path=$path;
		if(isset($array["controller"])){
			$this->fromArray($array);
		}else{
			$this->methods=\array_keys($array);
			$this->fromArray(\reset($array));
		}
		$this->id=\uniqid();
	}

	private function fromArray($array){
		$this->controller=$array["controller"];
		$this->action=$array["action"];
		$this->name=$array["name"];
		$this->cache=$array["cache"];
		$this->duration=$array["duration"];
		if(isset($array["parameters"]) && \sizeof($array["parameters"])>0){
			if(\class_exists($this->controller)){
				if(\method_exists($this->controller, $this->action)){
					$method=new \ReflectionMethod($this->controller,$this->action);
					$params=$method->getParameters();
					foreach ($array["parameters"] as $paramIndex){
						if($paramIndex==="*"){
							$pName=$this->getVariadicParam($params);
							if($pName!==false){
								$this->parameters[]="...".$pName;
							}
						}else{
							$index=\intval(\str_replace("~", "", $paramIndex));
							if(isset($params[$index])){
								if(\substr($paramIndex,0,1)==="~")
									$this->parameters[]=$params[$index]->getName();
								else
									$this->parameters[]=$params[$index]->getName()."*";
							}
						}
					}
				}else{
					$this->messages[]="The method <b>".$this->action."</b> does not exists in the class <b>".$this->controller."</b>.\n";
				}
			}else{
				$this->messages[$this->controller]="The class <b>".$this->controller."</b> does not exist.\n";
			}
		}
	}
	private function getVariadicParam($parameters){
		foreach ($parameters as $param){
			if($param->isVariadic()){
				return $param->getName();
			}
		}
		return false;
	}

	public function getPath() {
		return $this->path;
	}

	public function setPath($path) {
		$this->path=$path;
		return $this;
	}

	public function getController() {
		return $this->controller;
	}

	public function setController($controller) {
		$this->controller=$controller;
		return $this;
	}

	public function getAction() {
		return $this->action;
	}

	public function setAction($action) {
		$this->action=$action;
		return $this;
	}

	public function getParameters() {
		return $this->parameters;
	}

	public function setParameters($parameters) {
		$this->parameters=$parameters;
		return $this;
	}

	public function getCache() {
		return $this->cache;
	}

	public function setCache($cache) {
		$this->cache=$cache;
		return $this;
	}

	public function getDuration() {
		return $this->duration;
	}

	public function setDuration($duration) {
		$this->duration=$duration;
		return $this;
	}

	public function getName() {
		return $this->name;
	}

	public function setName($name) {
		$this->name=$name;
		return $this;
	}

	public function getCompiledParams(){
		return " (".((\is_array($this->parameters))?\implode(", ", $this->parameters):$this->parameters).")";
	}

	public static function init($array){
		$result=[];
		foreach ($array as $k=>$v){
			$result[]=new Route($k, $v);
		}
		return $result;
	}

	public function getId() {
		return $this->id;
	}

	public function getMessages() {
		return $this->messages;
	}

	public function getMethods() {
		return $this->methods;
	}

	public function setMethods($methods) {
		$this->methods=$methods;
		return $this;
	}
}

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
		if(isset($array['controller'])){
			$this->messages=[];
			$this->path=$path;
			$this->methods=$array['methods']??'';
			$this->fromArray($array);
			$this->id=\uniqid();
		}
	}
	
	private static function mergeRouteArray($routeArrays){
		$response=[];
		foreach ($routeArrays as $method=>$route){
			$routeName=$route['name'];
			if(!isset($response[$routeName])){
				$response[$routeName]=$route;
			}
			$response[$routeName]['methods'][]=$method;
		}
		return $response;
	}

	private function fromArray($array){
		$this->controller=$array["controller"];
		$this->action=$array["action"];
		$this->name=isset($array["name"])?$array["name"]:'';
		$this->cache=isset($array["cache"])?$array["cache"]:false;
		$this->duration=isset($array["duration"])?$array["duration"]:false;
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
			if(isset($v['controller'])){
				$result[]=new Route($k, $v);
			}else{
				$routes=self::mergeRouteArray($v);
				foreach ($routes as $route){
					$result[]=new Route($k,$route);
				}
			}
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

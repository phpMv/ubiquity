<?php

namespace micro\controllers\admin\popo;

class Route {
	private $path;
	private $controller;
	private $action;
	private $parameters=[];
	private $cache;
	private $duration;
	private $name;
	private $methods;

	public function __construct($path="",$array=[]){
		$this->path=$path;
		if(isset($array["controller"])){
			$this->fromArray($array);
		}else{
			$this->methods=\array_keys($array);
			$this->fromArray(\reset($array));
		}
	}

	private function fromArray($array){
		$this->controller=$array["controller"];
		$this->action=$array["action"];
		$this->name=$array["name"];
		$this->cache=$array["cache"];
		$this->duration=$array["duration"];
		if(\sizeof($array["parameters"])>0){
			$method=new \ReflectionMethod($this->controller,$this->action);
			$params=$method->getParameters();
			foreach ($array["parameters"] as $param){
				$this->parameters[]=$params[$param]->getName();
			}
		}
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

	public static function init($array){
		$result=[];
		foreach ($array as $k=>$v){
			$result[]=new Route($k, $v);
		}
		return $result;
	}
}

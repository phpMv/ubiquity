<?php
namespace micro\cache;
use micro\orm\parser\Reflexion;
use micro\utils\StrUtils;

class ControllerParser {
	private $controllerClass;
	private $mainRouteClass;
	private $routesMethods=[];

	public function parse($controllerClass){
		$this->controllerClass=$controllerClass;
		$reflect=new \ReflectionClass($controllerClass);
		if(!$reflect->isAbstract()){
			$instance=new $controllerClass();
			$annotsClass= Reflexion::getAnnotationClass($controllerClass, "@route");
			if(\sizeof($annotsClass)>0)
				$this->mainRouteClass=$annotsClass[0];
			$methods=Reflexion::getMethods($instance,\ReflectionMethod::IS_PUBLIC);
			foreach ($methods as $method){
				$annots=Reflexion::getAnnotationsMethod($controllerClass, $method->name, "@route");
				if($annots!==false)
					$this->routesMethods[$method->getName()]=["annotations"=>$annots,"method"=>$method];
			}
		}
	}

	private function cleanpath($prefix,$path=""){
		if(!StrUtils::endswith($prefix, "/"))
			$prefix=$prefix."/";
		if($path!=="" && StrUtils::startswith($path, "/"))
			$path=\substr($path, 1);
		$path=$prefix.$path;
		if(StrUtils::endswith($path, "/"))
			$path=\substr($path, 0,\strlen($path)-1);
		return $path;
	}

	public function asArray(){
		$result=[];
		$prefix="";$httpMethods=false;
		if($this->mainRouteClass){
			if(isset($this->mainRouteClass->path))
				$prefix=$this->mainRouteClass->path;
			if(isset($this->mainRouteClass->methods)){
				$httpMethods=$this->mainRouteClass->methods;
				if($httpMethods!==null){
					if(\is_string($httpMethods))
						$httpMethods=[$httpMethods];
				}
			}
		}

		foreach ($this->routesMethods as $method=>$arrayAnnotsMethod){
			$routeAnnotations=$arrayAnnotsMethod["annotations"];
			foreach ($routeAnnotations as $routeAnnotation){
				if(isset($routeAnnotation->path)){
					$parameters=[];
					$path=$routeAnnotation->path;
					preg_match_all('@\{(.+?)\}@s', $path, $matches);
					if(isset($matches[1]) && \sizeof($matches[1])>0){
						$path=\preg_quote($path);
						$params=Reflexion::getMethodParameters($arrayAnnotsMethod["method"]);
						foreach ($matches[1] as $paramMatch){
							$find=\array_search($paramMatch, $params);
							if($find!==false){
								$parameters[]=$find;
								$path=\str_replace("\{".$paramMatch."\}", "(.+?)", $path);
							}else{
								throw new \Exception("{$paramMatch} is not a parameter of the method ".$arrayAnnotsMethod["method"]->getName());
							}
						}
					}
					$path=$this->cleanpath($prefix,$path)."$";
					if(isset($routeAnnotation->methods) && \is_array($routeAnnotation->methods)){
						$this->createRouteMethod($result,$path,$routeAnnotation->methods,$method,$parameters);
					}elseif(\is_array($httpMethods)){
						$this->createRouteMethod($result,$path,$httpMethods,$method,$parameters);
					}else{
						$result[$path]=["controller"=>$this->controllerClass,"action"=>$method,"parameters"=>$parameters];
					}
				}
			}
		}
		return $result;
	}

	private function createRouteMethod(&$result,$path,$httpMethods,$method,$parameters){
		foreach ($httpMethods as $httpMethod){
				$result[$path][$httpMethod]=["controller"=>$this->controllerClass,"action"=>$method,"parameters"=>$parameters];
		}
	}
}

<?php
namespace micro\controllers;
use micro\cache\CacheManager;
use micro\utils\RequestUtils;
use micro\cache\ControllerParser;

class Router {
	private static $routes;

	public static function start(){
		self::$routes=CacheManager::getControllerCache();
	}

	public static function getRoute($path){
		$path="/".$path;
		foreach (self::$routes as $routePath=>$routeDetails){
			if (preg_match("@^".$routePath."$@s",$path,$matches)){
				if(!isset($routeDetails["controller"])){
					$method=RequestUtils::getMethod();
					if(isset($routeDetails[$method]))
						return self::getRouteUrlParts(["path"=>$routePath,"details"=>$routeDetails[$method]],$matches);
				}else
					return self::getRouteUrlParts(["path"=>$routePath,"details"=>$routeDetails],$matches);
			}
		}
		return false;
	}

	public static function getRouteUrlParts($routeArray,$params){
		$params=array_slice($params, 1);
		$result=[$routeArray["details"]["controller"],$routeArray["details"]["action"]];
		$paramsOrder=$routeArray["details"]["parameters"];
		foreach ($paramsOrder as $order){
			$result[]=$params[$order];
		}
		return $result;
	}

	public static function addRoute($path,$controller,$action="index",$methods=null,$name=""){
		self::addRouteToRoutes(self::$routes, $path, $controller,$action,$methods,$name);
	}

	public static function addRouteToRoutes(&$routesArray,$path,$controller,$action="index",$methods=null,$name=""){
		$result=[];
		$method=new \ReflectionMethod($controller,$action);
		ControllerParser::parseRouteArray($result, $controller, ["path"=>$path,"methods"=>$methods,"name"=>$name], $method, $action);
		foreach ($result as $k=>$v){
			$routesArray[$k]=$v;
		}
	}
}

<?php
namespace micro\controllers;
use micro\cache\CacheManager;
use micro\utils\RequestUtils;

class Router {
	private static $routes;

	public static function start(){
		self::$routes=CacheManager::getControllerCache();
	}

	public static function getRoute($path){
		$path="/".$path;
		foreach (self::$routes as $routePath=>$routeDetails){
			if (preg_match("@".$routePath."@s",$path,$matches)){
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
}

<?php

namespace Ubiquity\cache\parser;

use Ubiquity\utils\base\UString;

/**
 * Ubiquity\cache\parser$CallableParser
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
class CallableParser {

	public static function parseRouteArray(&$result, $callable, $routeArray, \ReflectionFunction $function, $prefix = "", $httpMethods = NULL) {
		$pathParameters = self::addParamsPath ( $routeArray ["path"], $function, $routeArray ["requirements"] );
		$name = $routeArray ["name"];
		if (! isset ( $name )) {
			$name = UString::cleanAttribute ( $pathParameters );
		}
		$cache = $routeArray ["cache"];
		$duration = $routeArray ["duration"];
		$path = $pathParameters ["path"];
		$parameters = $pathParameters ["parameters"];
		$priority = $routeArray ["priority"];
		$path = ControllerParser::cleanpath ( $prefix, $path );
		if (isset ( $routeArray ["methods"] ) && \is_array ( $routeArray ["methods"] )) {
			self::createRouteMethod ( $result, $callable, $path, $routeArray ["methods"], $parameters, $name, $cache, $duration, $priority );
		} elseif (\is_array ( $httpMethods )) {
			self::createRouteMethod ( $result, $callable, $path, $httpMethods, $parameters, $name, $cache, $duration, $priority );
		} else {
			$result [$path] = [ "controller" => $callable,"action" => "","parameters" => $parameters,"name" => $name,"cache" => $cache,"duration" => $duration,"priority" => $priority ];
		}
	}

	private static function createRouteMethod(&$result, $callable, $path, $httpMethods, $parameters, $name, $cache, $duration, $priority) {
		foreach ( $httpMethods as $httpMethod ) {
			$result [$path] [$httpMethod] = [ "controller" => $callable,"action" => "","parameters" => $parameters,"name" => $name,"cache" => $cache,"duration" => $duration,"priority" => $priority ];
		}
	}

	public static function addParamsPath($path, \ReflectionFunction $function, $requirements) {
		$parameters = [ ];
		$hasOptional = false;
		preg_match_all ( '@\{(\.\.\.|\~)?(.+?)\}@s', $path, $matches );
		if (isset ( $matches [2] ) && \sizeof ( $matches [2] ) > 0) {
			$path = \preg_quote ( $path );
			$params = $function->getParameters ();
			$index = 0;
			foreach ( $matches [2] as $paramMatch ) {
				$find = \array_search ( $paramMatch, $params );
				if ($find !== false) {
					$requirement = '.+?';
					if (isset ( $requirements [$paramMatch] )) {
						$requirement = $requirements [$paramMatch];
					}
					ControllerParser::scanParam ( $parameters, $hasOptional, $matches, $index, $paramMatch, $find, $path, $requirement );
				} else {
					throw new \Exception ( "{$paramMatch} is not a parameter of the function " . $function->name );
				}
				$index ++;
			}
		}
		if ($hasOptional)
			$path .= "/(.*?)";
		return [ "path" => $path,"parameters" => $parameters ];
	}
}


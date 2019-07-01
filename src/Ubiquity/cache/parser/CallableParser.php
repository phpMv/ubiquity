<?php

/**
 * Cache parsers
 */
namespace Ubiquity\cache\parser;

use Ubiquity\utils\base\UString;

/**
 * Ubiquity\cache\parser$CallableParser
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.1
 *
 */
class CallableParser {

	public static function parseRouteArray(&$result, $callable, $routeArray, \ReflectionFunction $function, $prefix = "", $httpMethods = NULL) {
		$pathParameters = ControllerParser::addParamsPath ( $routeArray ["path"], $function, $routeArray ["requirements"] );
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
}


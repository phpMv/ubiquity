<?php

namespace Ubiquity\controllers\rest;

use Ubiquity\utils\base\UString;

/**
 * Base class for Formating Rest responses.
 * Ubiquity\controllers\rest$ResponseFormatter
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
class ResponseFormatter {

	/**
	 * Returns a formated JSON response for an array of objects $objects
	 *
	 * @param array $objects
	 * @param array|null $pages
	 * @return string
	 */
	public function get($objects, $pages = null) {
		$objects = $this->getDatas ( $objects );
		return $this->format ( [ "datas" => $objects,"count" => \sizeof ( $objects ) ] );
	}

	/**
	 * Returns an array of datas from an array of objects
	 *
	 * @param array $objects
	 * @param string $classname
	 * @return array
	 */
	public function getDatas($objects, &$classname = null) {
		$objects = \array_map ( function ($o) use (&$classname) {
			return $this->cleanRestObject ( $o, $classname );
		}, $objects );
		return \array_values ( $objects );
	}

	public function getJSONDatas($datas) {
		return $this->toJson ( $this->getDatas ( $datas ) );
	}

	/**
	 * Returns the array of attributes corresponding to an object $o
	 *
	 * @param object $o
	 * @param string $classname
	 * @return array
	 */
	public function cleanRestObject($o, &$classname = null) {
		$o = $o->_rest;
		foreach ( $o as $k => $v ) {
			if (isset ( $v->_rest )) {
				$o [$k] = $v->_rest;
			}
			if (\is_array ( $v )) {
				foreach ( $v as $index => $values ) {
					if (isset ( $values->_rest ))
						$v [$index] = $this->cleanRestObject ( $values );
				}
				$o [$k] = $v;
			}
		}
		return $o;
	}

	/**
	 * Returns a formated JSON response for an object $object
	 *
	 * @param object $object
	 * @return string
	 */
	public function getOne($object) {
		return $this->format ( [ "data" => $this->cleanRestObject ( $object ) ] );
	}

	/**
	 * Formats a response array
	 *
	 * @param array $arrayResponse
	 * @return string
	 */
	public function format($arrayResponse) {
		return \json_encode ( $arrayResponse );
	}

	/**
	 * Returns the model name corresponding to $controlleName
	 *
	 * @param string $controllerName
	 * @return string
	 */
	public function getModel($controllerName) {
		$array = \explode ( "\\", $controllerName );
		$result = \ucfirst ( end ( $array ) );
		if (UString::endswith ( $result, "s" )) {
			$result = \substr ( $result, 0, - 1 );
		}
		return $result;
	}

	/**
	 * Formats an array of datas in JSON
	 *
	 * @param array $data
	 * @return string
	 */
	public function toJson($data) {
		return \json_encode ( $data );
	}

	/**
	 * Returns a JSON representation of the exception $e
	 *
	 * @param \Exception $e
	 * @return string
	 */
	public function formatException($e) {
		$error = new RestError ( @$e->getCode (), \utf8_encode ( $e->getMessage () ), @$e->getTraceAsString (), @$e->getFile (), 500 );
		return $this->format ( $error->asArray () );
	}

	public static function toXML($data, &$xml_data) {
		foreach ( $data as $key => $value ) {
			if (is_numeric ( $key )) {
				$key = 'item' . $key; // dealing with <0/>..<n/> issues
			}
			if (is_array ( $value )) {
				$subnode = $xml_data->addChild ( $key );
				self::toXML ( $value, $subnode );
			} else {
				$xml_data->addChild ( "$key", htmlspecialchars ( "$value" ) );
			}
		}
	}
}

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
	 *
	 * @param array $datas
	 * @param array|null $pages
	 * @return string
	 */
	public function get($datas, $pages = null) {
		$datas = $this->getDatas ( $datas );
		return $this->format ( [ "datas" => $datas,"count" => \sizeof ( $datas ) ] );
	}

	public function getDatas($datas, &$classname = null) {
		$datas = \array_map ( function ($o) use (&$classname) {
			return $this->cleanRestObject ( $o, $classname );
		}, $datas );
		return \array_values ( $datas );
	}

	public function getJSONDatas($datas) {
		return $this->toJson ( $this->getDatas ( $datas ) );
	}

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

	public function getOne($datas) {
		return $this->format ( [ "data" => $this->cleanRestObject ( $datas ) ] );
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

	public function getModel($controllerName) {
		$array = \explode ( "\\", $controllerName );
		$result = \ucfirst ( end ( $array ) );
		if (UString::endswith ( $result, "s" )) {
			$result = \substr ( $result, 0, - 1 );
		}
		return $result;
	}

	public function toJson($data) {
		return \json_encode ( $data );
	}

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

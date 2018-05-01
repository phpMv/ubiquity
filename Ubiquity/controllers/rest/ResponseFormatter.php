<?php

namespace Ubiquity\controllers\rest;

use Ubiquity\utils\base\UString;

class ResponseFormatter {

	/**
	 * @param array $datas
	 * @return array
	 */
	public function get($datas){
		$datas=$this->getDatas($datas);
		return $this->format(["datas"=>$datas,"count"=>\sizeof($datas)]);
	}
	
	public function getDatas($datas){
		$datas=\array_map(function($o){
			return $this->cleanRestObject($o);
		}, $datas);
		return \array_values($datas);
	}
	
	public function getJSONDatas($datas){
		return $this->toJson($this->getDatas($datas));
	}

	public function cleanRestObject($o){
		$o=$o->_rest;
		foreach ($o as $k=>$v){
			if(isset($v->_rest))
				$o[$k]=$v->_rest;
				if(\is_array($v)){
					foreach ($v as $index=>$values){
						if(isset($values->_rest))
							$v[$index]=$this->cleanRestObject($values);
					}
					$o[$k]=$v;
				}
		}
		return $o;
	}

	public function getOne($datas){
		return $this->format(["data"=>$this->cleanRestObject($datas)]);
	}

	/**
	 * Formats a response array
	 * @param array $arrayResponse
	 * @return string
	 */
	public function format($arrayResponse){
		return \json_encode($arrayResponse);
	}

	public function getModel($controllerName){
		$array=\explode("\\", $controllerName);
		$result= \ucfirst(end($array));
		if(UString::endswith($result, "s")){
			$result=\substr($result, 0,-1);
		}
		return $result;
	}

	public function toJson($data){
		return \json_encode($data);
	}

	public function formatException($e){
		return $this->format(["status"=>"error","message"=>\utf8_encode($e->getMessage()),"code"=>@$e->getCode()]);
	}

	public static function toXML($data,&$xml_data){
		foreach( $data as $key => $value ) {
			if( is_numeric($key) ){
				$key = 'item'.$key; //dealing with <0/>..<n/> issues
			}
			if( is_array($value) ) {
				$subnode = $xml_data->addChild($key);
				array_to_xml($value, $subnode);
			} else {
				$xml_data->addChild("$key",htmlspecialchars("$value"));
			}
		}
	}
}

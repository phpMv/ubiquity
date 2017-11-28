<?php

namespace micro\annotations\parser;


use micro\utils\StrUtils;

class DocParser {
	private $originalContent;
	private $lines;
	private $description;

	public function __construct($content){
		$this->originalContent=$content;
		$this->description=[];
		$this->lines=[];
	}
	public function parse(){
		$this->lines=\explode("\r\n", $this->originalContent);
		foreach ($this->lines as $line){
			$line=\trim($line);
			$line=\preg_replace("@^(\*\*\/)|(\/\*\*)|(\*)@i", "", $line);
			if(StrUtils::isNotNull($line)){
				$line=\trim($line);
				if(StrUtils::startswith($line, "@")){
					if(\preg_match("@^\@(.*?)\ @i", $line,$matches)){
						$this->addInArray($this->lines, $matches[1], \preg_replace("@^\@(.*?)\ @i", "", $line));
					}
				}else{
					$this->description[]=$line;
				}
			}
		}
		$this->description=\array_diff($this->description, ["","/"]);
		return $this;
	}

	public function isEmpty(){
		return \sizeof($this->lines)==0;
	}

	private function addInArray(&$array,$key,$value){
		if(!isset($array[$key])){
			$array[$key]=[];
		}
		$array[$key][]=$value;
	}

	public function getDescription(){
		return $this->description;
	}

	public function getPart($partKey){
		if(isset($this->lines[$partKey]))
			return $this->lines[$partKey];
		return null;
	}

	public function getDescriptionAsHtml($separator="<br>"){
		$desc=$this->getDescription();
		if(\sizeof($desc)>0)
			return \implode($separator, $desc);
		return null;
	}

	public function getMethodParams(){
		$result=[];
		if(isset($this->lines["param"])){
			$params=$this->lines["param"];
			foreach ($params as $param){
				$param.="   ";
				list($type, $name,$description) = explode(' ', $param, 3);
				$result[]=[$type,$name,$description];
			}
		}
		return $result;
	}

	public function getMethodParamsAsHtml($separator="<br>",$inlineSeparator="&nbsp;",...$functions){
		return self::getElementsAsHtml($this->getMethodParams(), $separator,$inlineSeparator,...$functions);
	}

	public function getMyMethodParamsAsHtml(){
		return self::getElementsAsHtml($this->getMethodParams(), null,"&nbsp;",function($p){return $p;},function($p){return $p;},function($p){return $p;});
	}

	public function getMyMethodResultAsHtml(){
		return self::getElementsAsHtml($this->getMethodReturn(), "<br>","&nbsp;",function($p){return $p;},function($p){return $p;});
	}

	public function getMethodReturn(){
		$result=[];
		if(isset($this->lines["return"])){
			$returns=$this->lines["return"];
			foreach ($returns as $return){
				$return.="  ";
				list($type,$description) = explode(' ', $return, 2);
				$result[]=[$type,$description];
			}
		}
		return $result;
	}

	public static function getElementsAsHtml($elements,$separator="<br>",$inlineSeparator="&nbsp;",...$functions){
		$result=[];
		$count=\sizeof($functions);
		foreach ($elements as $element){
			$part=[];
			for ($i=0;$i<$count;$i++){
				if(isset($element[$i])){
					$part[]=$functions[$i]($element[$i]);
				}
			}
			$result[]=\implode($inlineSeparator, $part);
		}
		if(isset($separator))
			return \implode($separator, $result);
		return $result;
	}

	public static function docClassParser($classname){
		if(\class_exists($classname)){
			$reflect=new \ReflectionClass($classname);
			return (new DocParser($reflect->getDocComment()))->parse();
		}
	}

	public static function docMethodParser($classname,$method){
		if(\class_exists($classname)){
			if(\method_exists($classname, $method)){
				$reflect=new \ReflectionMethod($classname,$method);
				return (new DocParser($reflect->getDocComment()))->parse();
			}
		}
	}
}

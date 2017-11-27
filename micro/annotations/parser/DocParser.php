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

	public function getPartAsHtml($partKey,$separator="<br>",$second=null){
		$part=$this->getPart($partKey);
		if(isset($part))
			return \implode($separator, $part);
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
				$reflect=new \ReflectionMethod($classname);
				return (new DocParser($reflect->getDocComment()))->parse();
			}
		}
	}
}

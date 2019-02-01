<?php

namespace Ubiquity\annotations\parser;


use Ubiquity\utils\base\UString;

class DocParser {
	private $originalContent;
	private $lines;
	private $description;
	/**
	 * @var DocFormater
	 */
	private $formater;

	public function __construct($content,DocFormater $formater=null){
		$this->originalContent=$content;
		$this->description=[];
		$this->lines=[];
		$this->formater=$formater;
	}
	public function parse(){
		$this->lines=\explode("\n", $this->originalContent);
		foreach ($this->lines as $line){
			$line=\trim($line);
			$line=\preg_replace("@^(\*\*\/)|(\/\*\*)|(\*)|(\/)@i", "", $line);
			if(UString::isNotNull($line)){
				$line=\trim($line);
				if(UString::startswith($line, "@")){
					if(\preg_match("@^\@(.*?)\ @i", $line,$matches)){
						$this->addInArray($this->lines, $matches[1], \preg_replace("@^\@".$matches[1]."(.*?)\ @i", "$1", $line));
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
		return [];
	}

	public function getDescriptionAsHtml($separator="<br>"){
		$descs=$this->getDescription();
		if(\sizeof($descs)>0){
			$descs=self::getElementsAsHtml($descs,null);
			if(isset($separator)){
				return \implode($separator, $descs);
			}
			return $descs;
		}
		return null;
	}

	public function getMethodParams(){
		return $this->getPart("param");
	}

	public function getMethodParamsReturn(){
		return \array_merge($this->getPart("param"),$this->getPart("return"));
	}

	public function getMethodReturn(){
		return $this->getPart("return");
	}

	public function getMethodParamsAsHtml($separator=NULL){
		return self::getElementsAsHtml($this->getMethodParams(), $separator);
	}

	public function getMethodParamsReturnAsHtml($separator=NULL){
		return self::getElementsAsHtml($this->getMethodParamsReturn(), $separator);
	}



	public function getMethodReturnAsHtml($separator="<br>"){
		return self::getElementsAsHtml($this->getMethodReturn(), $separator);
	}


	public function getElementsAsHtml($elements,$separator="<br>"){
		$result=[];
		foreach ($elements as $element){
			$result[]=$this->formater->replaceAll($element);
		}
		if(isset($separator))
			return \implode($separator, $result);
		return $result;
	}

	public static function docClassParser($classname){
		if(\class_exists($classname)){
			$reflect=new \ReflectionClass($classname);
			return (new DocParser($reflect->getDocComment(),new DocFormater()))->parse();
		}
	}

	public static function docMethodParser($classname,$method){
		if(\class_exists($classname)){
			if(\method_exists($classname, $method)){
				$reflect=new \ReflectionMethod($classname,$method);
				return (new DocParser($reflect->getDocComment(),new DocFormater()))->parse();
			}
		}
	}
}

<?php
namespace micro\orm\creator;
use micro\annotations\Id;
class Member {
	private $name;
	private $annotations;

	public function __construct($name){
		$this->name=$name;
		$this->annotations=array();
	}

	public function __toString(){
		$annotationsStr="";
		if(sizeof($this->annotations)>0){
			$annotationsStr="\t/**";
			$annotations=$this->annotations;
			\array_walk($annotations,function($item){return $item."";});
			$annotationsStr.=implode("\n\t* ", $annotations);
			$annotationsStr.="\t*/";
		}
		return $annotationsStr."\n\tprivate $".$this->name.";\n";
	}

	public function setPrimary(){
		$this->annotations=new Id();
	}
}
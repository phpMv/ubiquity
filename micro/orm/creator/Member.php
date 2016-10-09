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
		$annotations="";
		if(sizeof($this->annotations)>0){
			$annotations="\t/**";
			$annotations.=implode("\n\t* ", \array_walk($this->annotations,function($item){return $item."";}));
			$annotations.="\t*/";
		}
		return $annotations."\n\tprivate $".$this->name.";\n";
	}

	public function setPrimary(){
		$this->annotations=new Id();
	}
}
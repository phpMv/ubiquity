<?php
namespace micro\orm\creator;
class Member {
	private $name;
	private $primary;
	private $manyToOne;

	private $annotations;

	public function __construct($name){
		$this->name=$name;
		$this->annotations=array();
		$this->primary=false;
		$this->manyToOne=false;
	}

	public function __toString(){
		$annotationsStr="";
		if(sizeof($this->annotations)>0){
			$annotationsStr="\n\t/**";
			$annotations=$this->annotations;
			\array_walk($annotations,function($item){return $item."";});
			if(\sizeof($annotations)>1){
				$annotationsStr.=implode("\n\t * ", $annotations);
			}else{
				$annotationsStr.="\n\t * ".$annotations[0];
			}
			$annotationsStr.="\n\t*/";
		}
		return $annotationsStr."\n\tprivate $".$this->name.";\n";
	}

	public function setPrimary(){
		if($this->primary===false){
			$this->annotations[]=new \Id();
			$this->primary=true;
		}
	}

	public function addManyToOne($name,$className,$nullable=false){
		$this->annotations[]=new \ManyToOne();
		$joinColumn=new \JoinColumn();
		$joinColumn->name=$name;
		$joinColumn->className=$className;
		$joinColumn->nullable=$nullable;
		$this->annotations[]=$joinColumn;
		$this->manyToOne=true;
	}

	public function addOneToMany($mappedBy,$className){
		$oneToMany=new \OneToMany();
		$oneToMany->mappedBy=$mappedBy;
		$oneToMany->className=$className;
		$this->annotations[]=$oneToMany;
	}

	public function getName() {
		return $this->name;
	}

	public function isManyToOne() {
		return $this->manyToOne;
	}

	public function isPrimary() {
		return $this->primary;
	}



}
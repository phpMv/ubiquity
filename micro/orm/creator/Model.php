<?php
namespace micro\orm\creator;
class Model {
	private $members;
	private $name;

	public function __construct($name){
		$this->name=$name;
		$this->members=array();
	}

	public function addMember($member){
		$this->members[]=$member;
		return $this;
	}

	public function __toString(){
		$result="<?php\nclass ".ucfirst($this->name)."{";
		$result.=implode("\n", \array_walk($this->members,function($item){return $item."";}));
		$result.="\n}";
		return $result;
	}
}
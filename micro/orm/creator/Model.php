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
		$result.=implode("\n", $this->members);
		$result.="\n}";
		return $result;
	}
}
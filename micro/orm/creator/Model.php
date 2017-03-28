<?php
namespace micro\orm\creator;
class Model {
	private $members;
	private $name;

	public function __construct($name){
		$this->name=\ucfirst($name);
		$this->members=array();
	}

	public function addMember(Member $member){
		$this->members[$member->getName()]=$member;
		return $this;
	}

	public function addManyToOne($member,$name,$className,$nullable=false){
		if(\array_key_exists($member, $this->members)===false){
			$this->addMember(new Member($member));
			$this->removeMember($name);
		}
		$this->members[$member]->addManyToOne($name,$className,$nullable);
	}

	public function removeMember($memberName){
		if($this->members[$memberName]->isPrimary()===false)
			unset($this->members[$memberName]);
	}

	public function addOneToMany($member,$mappedBy,$className){
		if(\array_key_exists($member, $this->members)===false){
			$this->addMember(new Member($member));
		}
		$this->members[$member]->addOneToMany($mappedBy,$className);
	}

	public function addManyToMany($member,$targetEntity,$inversedBy,$joinTable){
		if(\array_key_exists($member, $this->members)===false){
			$this->addMember(new Member($member));
		}
		$this->members[$member]->addManyToMany($targetEntity,$inversedBy,$joinTable);
	}

	public function __toString(){
		$result="<?php\nclass ".ucfirst($this->name)."{";
		$members=$this->members;
		\array_walk($members,function($item){return $item."";});
		$result.=implode("", $members);
		foreach ($members as $member){
			$result.=$member->getGetter();
			$result.=$member->getSetter();
		}
		$result.="\n}";
		return $result;
	}

	public function getName() {
		return $this->name;
	}

	public function isAssociation(){
		$count=0;
		foreach ($this->members as $member){
			if($member->isManyToOne()===true || $member->isPrimary()===true){
				$count++;
			}
		}
		return $count==\sizeof($this->members);
	}

	public function getManyToOneMembers(){
		$result=array();
		foreach ($this->members as $member){
			if($member->isManyToOne()===true){
				$result[]=$member;
			}
		}
		return $result;
	}

}

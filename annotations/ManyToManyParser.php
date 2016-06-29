<?php
namespace micro\annotations;

use micro\orm\Reflexion;
use micro\orm\OrmUtils;
/**
 * Annotation ManyToManyParser
 * @author jc
 * @version 1.0.0.1
 * @package annotations
 */

class ManyToManyParser{
	private $member;
	private $joinTable;
	private $myFkField;
	private $fkField;
	private $targetEntity;
	private $targetEntityClass;
	private $targetEntityTable;
	private $myPk;
	private $inversedBy;
	private $pk;
	private $instance;
	public function __construct($instance,$member){
		$this->instance=$instance;
		$this->member=$member;
	}

	public function init(){
		$member=$this->member;
		$instance=$this->instance;
		$class=get_class($instance);
		$annot=Reflexion::getAnnotationMember($class, $member, "ManyToMany");
		if($annot!=false){
			$this->targetEntity=$annot->targetEntity;
			$this->inversedBy=strtolower($this->targetEntity)."s";
			if(!is_null($annot->inversedBy))
				$this->inversedBy=$annot->inversedBy;
			$this->targetEntityClass=get_class(new $this->targetEntity());

			$annotJoinTable=Reflexion::getAnnotationMember($class, $member, "JoinTable");
			$this->joinTable=$annotJoinTable->name;
			$joinColumnsAnnot=$annotJoinTable->joinColumns;
			$this->myFkField="id".OrmUtils::getTableName($class);
			$this->myPk=OrmUtils::getFirstKey($class);
			if(!is_null($joinColumnsAnnot)){
				$this->myFkField=$joinColumnsAnnot->name;
				$this->myPk=$joinColumnsAnnot->referencedColumnName;
			}
			$this->targetEntityTable=OrmUtils::getTableName($this->targetEntity);
			$this->fkField="id".ucfirst($this->targetEntityTable);
			$this->pk=OrmUtils::getFirstKey($this->targetEntityClass);
			$inverseJoinColumnsAnnot=$annotJoinTable->inverseJoinColumns;
			if(!is_null($inverseJoinColumnsAnnot)){
				$this->fkField=$inverseJoinColumnsAnnot->name;
				$this->pk=$inverseJoinColumnsAnnot->referencedColumnName;
			}
		return true;
		}
		return false;

	}

	public function getMember() {
		return $this->member;
	}

	public function setMember($member) {
		$this->member = $member;
		return $this;
	}

	public function getJoinTable() {
		return $this->joinTable;
	}

	public function setJoinTable($joinTable) {
		$this->joinTable = $joinTable;
		return $this;
	}

	public function getMyFkField() {
		return $this->myFkField;
	}

	public function setMyFkField($myFkField) {
		$this->myFkField = $myFkField;
		return $this;
	}

	public function getFkField() {
		return $this->fkField;
	}

	public function setFkField($fkField) {
		$this->fkField = $fkField;
		return $this;
	}

	public function getTargetEntity() {
		return $this->targetEntity;
	}

	public function setTargetEntity($targetEntity) {
		$this->targetEntity = $targetEntity;
		return $this;
	}

	public function getTargetEntityClass() {
		return $this->targetEntityClass;
	}

	public function setTargetEntityClass($targetEntityClass) {
		$this->targetEntityClass = $targetEntityClass;
		return $this;
	}

	public function getTargetEntityTable() {
		return $this->targetEntityTable;
	}

	public function setTargetEntityTable($targetEntityTable) {
		$this->targetEntityTable = $targetEntityTable;
		return $this;
	}

	public function getMyPk() {
		return $this->myPk;
	}

	public function setMyPk($myPk) {
		$this->myPk = $myPk;
		return $this;
	}

	public function getPk() {
		return $this->pk;
	}

	public function setPk($pk) {
		$this->pk = $pk;
		return $this;
	}

	public function getInversedBy() {
		return $this->inversedBy;
	}

	public function setInversedBy($inversedBy) {
		$this->inversedBy = $inversedBy;
		return $this;
	}

	public function getInstance() {
		return $this->instance;
	}

	public function setInstance($instance) {
		$this->instance = $instance;
		return $this;
	}


}
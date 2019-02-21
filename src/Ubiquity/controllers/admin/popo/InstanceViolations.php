<?php

namespace Ubiquity\controllers\admin\popo;

use Ubiquity\contents\validation\validators\ConstraintViolation;

class InstanceViolations {
	protected $instance;
	/**
	 * @var ConstraintViolation[]
	 */
	protected $violations;
	
	
	public static function init($instance,$violations){
		$result=new InstanceViolations();
		$result->setInstance($instance);
		$result->setViolations($violations);
		return $result;
	}
	
	public static function initFromArray($instancesViolations){
		$result=[];
		foreach ($instancesViolations as $instanceViolations){
			$result[]=self::init($instanceViolations[0], $instanceViolations[1]);
		}
		return $result;
	}
	/**
	 * @return mixed
	 */
	public function getInstance() {
		return $this->instance;
	}

	/**
	 * @return multitype:\Ubiquity\contents\validation\validators\ConstraintViolation 
	 */
	public function getViolations() {
		return $this->violations;
	}

	/**
	 * @param mixed $instance
	 */
	public function setInstance($instance) {
		$this->instance = $instance;
	}

	/**
	 * @param multitype:\Ubiquity\contents\validation\validators\ConstraintViolation  $violations
	 */
	public function setViolations($violations) {
		$this->violations = $violations;
	}
	
	public function __toString(){
		return $this->instance.'';
	}

}


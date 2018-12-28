<?php

namespace Ubiquity\annotations;

use Ubiquity\contents\validation\ValidatorsManager;

/**
 * Validator annotation
 * @author jc
 * @version 1.0.0.1
 * @package annotations
 * @usage('property'=>true, 'inherited'=>true, 'multiple'=>true)
 */
class ValidatorAnnotation extends BaseAnnotation {
	public $type;
	public $message;
	public $severity;
	public $constraints=[];
	
	/**
	 * Initialize the annotation.
	 */
	public function initAnnotation(array $properties){
		if (isset($properties[0])) {
			$this->type = $properties[0];
			unset($properties[0]);
			if(isset($properties[1])){
				if(!is_array($properties[1])){
					$this->constraints=["ref"=>$properties[1]];
				}else{
					$this->constraints=$properties[1];
				}
				unset($properties[1]);
			}
		}else{
			throw new \Exception('Validator annotation must have a type');
		}
		parent::initAnnotation($properties);
		if (!isset(ValidatorsManager::$validatorTypes[$this->type])) {
			throw new \Exception('This type of annotation does not exists : '.$this->type);
		}
	}
}

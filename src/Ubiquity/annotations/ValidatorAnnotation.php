<?php

namespace Ubiquity\annotations;

use Ubiquity\contents\validation\ValidatorsManager;
use Ubiquity\utils\base\UArray;

/**
 * Validator annotation.
 * usages :
 * - validator("type"=>"validatorType")
 * - validator("type"=>"validatorType","message"=>"validatorMessage")
 * - validator("type"=>"validatorType","severity"=>"level")
 * - validator("type"=>"validatorType","group"=>"validatorGroup")
 * - validator("type"=>"validatorType","constraints"=>[constraints])
 *
 * @author jc
 * @version 1.0.0.1
 * @package annotations
 * @usage('property'=>true, 'inherited'=>true, 'multiple'=>true)
 */
class ValidatorAnnotation extends BaseAnnotation {
	public $type;
	public $message;
	public $severity;
	public $group;
	public $constraints = [ ];

	/**
	 * Initialize the annotation.
	 */
	public function initAnnotation(array $properties) {
		if (isset ( $properties [0] )) {
			$this->type = $properties [0];
			unset ( $properties [0] );
			if (isset ( $properties [1] )) {
				if (! is_array ( $properties [1] )) {
					$this->constraints = [ "ref" => $properties [1] ];
				} else {
					$this->constraints = $properties [1];
				}
				unset ( $properties [1] );
			}
		} else {
			throw new \Exception ( 'Validator annotation must have a type' );
		}
		parent::initAnnotation ( $properties );
		if (! isset ( ValidatorsManager::$validatorTypes [$this->type] )) {
			throw new \Exception ( 'This type of annotation does not exists : ' . $this->type );
		}
	}

	public static function initializeFromModel($type, $ref = null, $constraints = []) {
		$validator = new ValidatorAnnotation ();
		if (! is_array ( $constraints )) {
			$constraints = [ ];
		}
		if (isset ( $ref )) {
			$constraints ["ref"] = $ref;
		}
		$validator->type = $type;
		$validator->constraints = $constraints;
		return $validator;
	}

	protected function asAnnotation() {
		$fields = $this->getPropertiesAndValues ();
		$result = [ ];
		$result [] = $fields ["type"];
		unset ( $fields ["type"] );
		if (isset ( $fields ["constraints"] ) && isset ( $fields ["constraints"] ["ref"] )) {
			$result [] = $fields ["constraints"] ["ref"];
			unset ( $fields ["constraints"] ["ref"] );
		}
		if (sizeof ( $fields ) > 0) {
			foreach ( $fields as $field => $value ) {
				if ((is_array ( $value ) && sizeof ( $value ) > 0) || ! is_array ( $value )) {
					$result [$field] = $value;
				}
			}
		}
		return UArray::asPhpArray ( $result );
	}
}

<?php

namespace Ubiquity\contents\validation\validators\comparison;

use Ubiquity\contents\validation\validators\ValidatorHasNotNull;
use Ajax\semantic\components\validation\CustomRule;

/**
 * Ubiquity\contents\validation\validators\comparison$GreaterThanValidator
 * This class is part of Ubiquity
 *
 * @author jc
 * @version 1.0.0
 *
 */
class GreaterThanValidator extends ValidatorHasNotNull {
	protected $ref;

	public function __construct() {
		$this->message = 'This value should be greater than `{ref}`';
	}

	public function validate($value) {
		parent::validate ( $value );
		if ($this->notNull !== false) {
			return $value > $this->ref;
		}
		return true;
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\contents\validation\validators\Validator::getParameters()
	 */
	public function getParameters(): array {
		return [ 'ref','value' ];
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\contents\validation\validators\Validator::asUI()
	 */
	public function asUI(): array {
		$rule = new CustomRule ( 'greaterthan', "function(v,greaterThan){ return v>greaterThan;}", $this->_getMessage (), $this->ref );
		return \array_merge_recursive ( parent::asUI (), [ 'rules' => [ $rule ] ] );
	}
}


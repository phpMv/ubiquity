<?php

namespace Ubiquity\contents\validation\validators\comparison;

use Ubiquity\contents\validation\validators\ValidatorHasNotNull;

/**
 *
 * Ubiquity\contents\validation\validators\comparison$MatchWithValidator
 * This class is part of Ubiquity
 *
 * @author jc
 * @version 1.0.0
 *
 */
class MatchWithValidator extends ValidatorHasNotNull {
	protected $field;
	public static ?array $values;

	public function __construct() {
		$this->message = 'This value should be equals to `{field}` content.';
	}

	public function validate($value) {
		parent::validate ( $value );
		$values = self::$values ?? $_POST;
		if ($this->notNull !== false) {
			return $value == $values [$this->field] ?? null;
		}
		return true;
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\contents\validation\validators\Validator::getParameters()
	 */
	public function getParameters(): array {
		return [ 'field','value' ];
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\contents\validation\validators\Validator::asUI()
	 */
	public function asUI(): array {
		return \array_merge_recursive ( parent::asUI (), [ 'rules' => [ [ 'type' => 'match','prompt' => $this->_getMessage (),'value' => $this->field ] ] ] );
	}
}


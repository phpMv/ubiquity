<?php

namespace Ubiquity\contents\validation\validators\multiples;

use Ubiquity\contents\validation\validators\Validator;
use Ubiquity\utils\base\UString;
use Ubiquity\exceptions\ValidatorException;
use Ubiquity\contents\validation\validators\HasNotNullInterface;

abstract class ValidatorMultiple extends Validator implements HasNotNullInterface {
	protected $violation;
	protected $notNull;

	public function __construct() {
		$this->message = [ 'notNull' => 'This value should not be null' ];
		$this->notNull = false;
	}

	public function validate($value) {
		if (null == $value) {
			if ($this->notNull === true) {
				$this->violation = 'notNull';
				return false;
			} else {
				return;
			}
		}
		if (! UString::isValid ( $value )) {
			throw new ValidatorException ( 'This value can not be converted to string' );
		}
		return true;
	}

	/**
	 *
	 * @return array|string
	 */
	protected function mergeMessages() {
		if (! isset ( $this->modifiedMessage )) {
			return $this->message;
		} else {
			if (\is_array ( $this->modifiedMessage ) && \is_array ( $this->message )) {
				return \array_merge ( $this->message, $this->modifiedMessage );
			} else {
				return $this->modifiedMessage;
			}
		}
	}

	protected function _getMessage() {
		$parameters = $this->getParameters ();
		$message = $this->mergeMessages ();
		if (isset ( $this->violation ) && \is_array ( $message )) {
			$message = $this->_getMessageViolation ( $message );
		}
		foreach ( $parameters as $param ) {
			$message = \str_replace ( '{' . $param . '}', $this->$param??'', $message );
		}
		return $message;
	}

	protected function _getMessageViolation($messages) {
		if (isset ( $messages [$this->violation] )) {
			return $messages [$this->violation];
		}
		return \current ( $messages );
	}

	public function asUI(): array {
		if ($this->notNull) {
			return [ 'rules' => [[ 'type'=>'empty','prompt'=>$this->mergeMessages()['notNull']??'' ]] ];
		}
		return [ ];
	}
}


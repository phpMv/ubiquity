<?php

namespace Ubiquity\contents\validation\validators\multiples;

/**
 * Validate int identifiers (notNull positive integer).
 * @author jc
 */
class IdValidator extends ValidatorMultiple {
	protected $autoinc;
	public function __construct(){
		parent::__construct();
		$this->message=array_merge($this->message,[
				'positive'=>'This value must be positive',
				'type'=>'This value must be an integer'
		]);
	}
	
	public function validate($value) {
		if (!parent::validate($value)) {
			return false;
		}
		if($value!=(int)$value){
			$this->violation='type';
			return false;
		}
		if($value<=0){
			$this->violation='positive';
			return false;
		}
		return true;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Ubiquity\contents\validation\validators\Validator::getParameters()
	 */
	public function getParameters(): array {
		return ['value'];
	}
	/**
	 * {@inheritDoc}
	 * @see \Ubiquity\contents\validation\validators\Validator::setParams()
	 */
	protected function setParams(array $params) {
		parent::setParams($params);
		if($this->autoinc===true){
			$this->notNull=false;
		}
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\contents\validation\validators\Validator::asUI()
	 */
	public function asUI(): array {
		$rules[] = ['type' => 'regExp', 'prompt' => $this->_getMessage()['positive'], 'value' => '^[1-9]+$|^$'];
		return \array_merge_recursive(parent::asUI () , ['inputType'=>'number','rules' => $rules]);
	}
}


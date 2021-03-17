<?php

/**
 * Validation managment
 */
namespace Ubiquity\contents\validation;

use Ubiquity\db\utils\DbTypes;
use Ubiquity\contents\validation\validators\HasNotNullInterface;
use Ubiquity\annotations\AnnotationsEngineInterface;

class ValidationModelGenerator {
	protected $type;
	protected $name;
	protected $notNull;
	protected $primary;
	protected $modelContainer;
	/**
	 * @var AnnotationsEngineInterface
	 */
	protected $annotsEngine;

	public function __construct($container,$annotsEngine,$type, $name, $notNull, $primary) {
		$this->modelContainer=$container;
		$this->annotsEngine=$annotsEngine;
		$this->type = $type;
		$this->name = $name;
		$this->notNull = $notNull;
		$this->primary = $primary;
	}

	protected function parseType($type, $size) {
		switch ($type) {
			case 'tinyint' :
				if ($size == 1) {
					return $this->getValidatorAnnotFromModel( 'isBool' );
				}
				break;
			case 'boolean':case 'bool':
				return $this->getValidatorAnnotFromModel( 'isBool' );
			case 'date' :
				return $this->getValidatorAnnotFromModel ( 'type', 'date' );
			case 'datetime' :
				return $this->getValidatorAnnotFromModel ( 'type', 'dateTime' );
			case 'time' :
				return $this->getValidatorAnnotFromModel ( 'type', 'time' );
		}
		return null;
	}

	protected function getValidatorAnnotFromModel($type, $ref = null, $constraints = []){
		if (! is_array ( $constraints )) {
			$constraints = [ ];
		}
		if (isset ( $ref )) {
			$constraints ["ref"] = $ref;
		}
		return $this->annotsEngine->getAnnotation($this->modelContainer,'validator',\compact('type','constraints'));
	}

	protected function parseSize($type, $size) {
		if (isset ( $size )) {
			if (DbTypes::isString ( $type )) {
				return $this->getValidatorAnnotFromModel ( 'length', null, [ 'max' => $size ] );
			}
		}
		return null;
	}

	protected function parseNotNull(&$validatorAnnots) {
		if ($this->notNull) {
			$notNullAffected = false;
			$size = \count ( $validatorAnnots );
			$i = 0;
			while ( $i < $size && ! $notNullAffected ) {
				$validatorAnnot = $validatorAnnots [$i];
				$validatorClass = ValidatorsManager::$validatorTypes [$validatorAnnot->type];
				if (is_subclass_of ( $validatorClass, HasNotNullInterface::class, true )) {
					$validatorAnnots [$i]->constraints ["notNull"] = true;
					$notNullAffected = true;
				}
				$i ++;
			}
			if (! $notNullAffected) {
				$validatorAnnots [] = $this->getValidatorAnnotFromModel ( 'notNull' );
			}
		}
	}

	protected function parseName() {
		switch ($this->name) {
			case 'email' :
			case 'mail' :
				return $this->getValidatorAnnotFromModel ( 'email' );
			case 'url' :
				return $this->getValidatorAnnotFromModel ( 'url' );
		}
		return null;
	}

	protected function scanType(&$type, &$size) {
		$type = DbTypes::getType ( $this->type );
		$size = DbTypes::getSize ( $this->type );
	}

	public function parse() {
		if ($this->primary && DbTypes::isInt ( $this->type )) {
			return [ $this->getValidatorAnnotFromModel ( 'id', null, [ 'autoinc' => true ] ) ];
		}
		$validatorAnnot = $this->parseName ();
		$this->scanType ( $type, $size );
		if (! isset ( $validatorAnnot )) {
			$validatorAnnot = $this->parseType ( $type, $size );
		}

		$result = [ ];
		if (isset ( $validatorAnnot )) {
			$result [] = $validatorAnnot;
		}
		$validatorAnnot = $this->parseSize ( $type, $size );
		if (isset ( $validatorAnnot )) {
			$result [] = $validatorAnnot;
		}
		$this->parseNotNull ( $result );
		return $result;
	}
}

